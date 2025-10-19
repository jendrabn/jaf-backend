<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegionController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkirService) {}

    public function provinces(): JsonResponse
    {
        $provinces = $this->rajaOngkirService->fetchProvinces();

        return response()
            ->json($provinces, Response::HTTP_OK);
    }

    public function cities(int $provinceId): JsonResponse
    {
        $cities = $this->rajaOngkirService->fetchCities($provinceId);

        return response()->json($cities, Response::HTTP_OK);
    }

    public function districts(int $cityId): JsonResponse
    {
        $districts = $this->rajaOngkirService->fetchDistricts($cityId);

        return response()->json($districts, Response::HTTP_OK);
    }

    public function subdistricts(int $districtId): JsonResponse
    {
        $subdistricts = $this->rajaOngkirService->fetchSubDistricts($districtId);

        return response()->json($subdistricts, Response::HTTP_OK);
    }
}
