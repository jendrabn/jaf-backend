<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CourierDataTable;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CourierDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.couriers.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Courier $courier, Request $request): JsonResponse
    {
        $courier->update([
            'is_active' => $courier->is_active ? false : true,
        ]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
