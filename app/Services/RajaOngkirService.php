<?php

namespace App\Services;

use App\Models\Courier;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class for fetching shipping costs from RajaOngkir
 * @see https://rajaongkir.com/api/docs/shipping-cost/endpoint-rajaongkir-for-form-base-calculate-cost/about
 */
class RajaOngkirService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('shop.rajaongkir.base_url');
        $this->apiKey = config('shop.rajaongkir.key');
    }

    /**
     * Fetch shipping costs from RajaOngkir API .
     *
     * @param int $destination Destination District ID
     * @param int $weight Weight in grams
     * @return array|null
     */
    public function calculateCost(int $destination, int $weight, string|null $courier = null): array | null
    {
        if (is_null($courier)) {
            $courier = Courier::query()->active()->get()->pluck('code')->toArray();
            $courier = implode(':', $courier);
        }
        $courierHash = md5($courier);
        $origin = config('shop.address.district_id');

        $cacheKey = 'rajaongkir:cost:origin_' . $origin . ':destination_' . $destination . ':weight_' . $weight . ':courier_' . $courierHash;
        $cacheTtl = 24 * 60 * 60;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($destination, $weight, $courier, $origin) {

            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $this->apiKey)
                    ->asForm()
                    ->post("$this->baseUrl/calculate/district/domestic-cost", [
                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $courier
                    ]);

                $response->throwIf(! $response->ok());

                $result = $response->json('data');

                $result = collect($result)->map(
                    function ($cost) {
                        return [
                            'courier' => $cost['code'],
                            'courier_name' => $cost['name'],
                            'service' => $cost['service'],
                            'service_name' => $cost['description'],
                            'cost' => $cost['cost'],
                            'etd' => $cost['etd'],
                        ];
                    }
                );

                return $result->toArray();
            } catch (Exception $exception) {
                Log::error('Exception while fetching shipping costs', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                return null;
            }
        });
    }


    /**
     * Fetch provinces from RajaOngkir API.
     *
     * @return array|null
     */
    public function fetchProvinces(): array|null
    {
        $cacheKey = 'rajaongkir:provinces';
        $cacheTtl = 24 * 60 * 60;

        return Cache::remember($cacheKey,  $cacheTtl, function () {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $this->apiKey)
                    ->get("$this->baseUrl/destination/province");

                $response->throwIf(! $response->ok());

                $result = $response->json('data');

                return $result;
            } catch (Exception $exception) {
                Log::error('Exception while fetching provinces', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                return null;
            }
        });
    }

    /**
     * Fetch cities from RajaOngkir API.
     *
     * @param int $provinceId
     *
     * @return array|null
     */
    public function fetchCities(int $provinceId): array|null
    {
        $cacheKey = 'rajaongkir:cities:province_' . $provinceId;
        $cacheTtl = 24 * 60 * 60;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($provinceId) {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $this->apiKey)
                    ->get("$this->baseUrl/destination/city/$provinceId");

                $response->throwIf(! $response->ok());

                return $response->json('data');
            } catch (Exception $exception) {
                Log::error('Exception while fetching cities', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                return null;
            }
        });
    }

    /**
     * Fetch districts from RajaOngkir API.
     *
     * @param int $cityId
     *
     * @return array|null
     */
    public function fetchDistricts(int $cityId): array|null
    {
        $cacheKey = 'rajaongkir:districts:city_' . $cityId;
        $cacheTtl = 24 * 60 * 60;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($cityId) {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $this->apiKey)
                    ->get("$this->baseUrl/destination/district/$cityId");

                $response->throwIf(! $response->ok());

                return $response->json('data');
            } catch (Exception $exception) {
                Log::error('Exception while fetching districts', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                return null;
            }
        });
    }

    /**
     * Fetch sub districts from RajaOngkir API [Only for premium account of RajaOngkir].
     *
     * @param int $districtId
     *
     * @return array|null
     */
    public function fetchSubDistricts(int $districtId): array|null
    {
        $cacheKey = 'rajaongkir:subdistricts:district_' . $districtId;
        $cacheTtl = 24 * 60 * 60;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($districtId) {
            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->withHeader('key', $this->apiKey)
                    ->get("$this->baseUrl/destination/sub-district/$districtId");

                $response->throwIf(! $response->ok());

                return $response->json('data');
            } catch (Exception $exception) {
                Log::error('Exception while fetching subdistricts', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                return null;
            }
        });
    }
}
