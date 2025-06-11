<?php

namespace App\Services;

use App\Models\Shipping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RajaOngkirService
{
    public function getCosts(int $destination, int $weight, array $couriers = Shipping::COURIERS): array
    {
        $costs = [];
        foreach ($couriers as $courier) {
            $result = $this->fetchCosts($destination, $weight, $courier);

            if (!empty($result) || !is_null($result)) {
                $costs = array_merge($costs, $result);
            }
        }

        return $costs;
    }

    public function getService(string $service, int $destination, int $weight, string $courier): array|null
    {
        $costs = $this->fetchCosts($destination, $weight, $courier);

        return collect($costs)->firstWhere('service', $service);
    }

    public function fetchCosts(int $destination, int $weight, string $courier): array
    {
        $cacheKey = "shipping_costs_{$destination}_{$weight}_{$courier}";

        return Cache::remember($cacheKey, 3600, function () use ($destination, $weight, $courier) {
            try {
                $baseUrl = config('shop.rajaongkir.base_url');
                $apiKey = config('shop.rajaongkir.key');
                $originCityId = config('shop.address.city_id');

                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $apiKey)
                    ->post("$baseUrl/cost", [
                        'origin' => $originCityId,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $courier
                    ]);

                if ($response->failed()) {
                    return [];
                }

                $result = $response->json('rajaongkir.results.0');

                if (empty($result['costs'])) {
                    return [];
                }

                return collect($result['costs'])->map(fn($cost) => [
                    'courier' => $courier,
                    'courier_name' => $result['name'],
                    'service' => $cost['service'],
                    'service_name' => $cost['description'],
                    'cost' => $cost['cost'][0]['value'],
                    'etd' => trim(strtolower($cost['cost'][0]['etd'])) . ' hari',
                ])->toArray();
            } catch (\Throwable $exception) {
                Log::error($exception->getMessage());

                return [];
            }
        });
    }

}
