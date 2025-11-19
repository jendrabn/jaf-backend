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
        $flashSales = FlashSale::query()
            ->runningNow()
            ->with([
                'products.media',
                'products.flashSales',
            ])
            ->orderBy('end_at')
            ->get();

        return FlashSaleResource::collection($flashSales)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
