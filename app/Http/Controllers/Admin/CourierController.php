<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CourierDataTable;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param CourierDataTable $dataTable
     * @return View
     */
    public function index(CourierDataTable $dataTable): View
    {
        return $dataTable->render('admin.couriers.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Courier $courier
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Courier $courier, Request $request): JsonResponse
    {
        $courier->update([
            'is_active' => $courier->is_active ? false : true
        ]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
