<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FlashSaleResource;
use App\Models\FlashSale;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FlashSaleController extends Controller
{
    public function index(): JsonResponse
    {
        $relations = [
            'products.media',
            'products.flashSales',
        ];

        $runningFlashSales = FlashSale::query()
            ->runningNow()
            ->with($relations)
            ->orderBy('end_at')
            ->limit(1)
            ->get();

        $scheduledLimit = max(0, min(2, 3 - $runningFlashSales->count()));

        $upcomingFlashSales = $scheduledLimit === 0
            ? collect()
            : FlashSale::query()
                ->scheduled()
                ->with($relations)
                ->orderBy('start_at')
                ->limit($scheduledLimit)
                ->get();

        $flashSales = $runningFlashSales
            ->concat($upcomingFlashSales)
            ->values();

        return FlashSaleResource::collection($flashSales)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
