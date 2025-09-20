<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\TaxesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxRequest;
use App\Models\Tax;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaxController extends Controller
{
    public function index(TaxesDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.taxes.index');
    }

    public function create(): View
    {
        return view('admin.taxes.partials.modal', [
            'mode' => 'create',
            'tax' => null,
            'action' => route('admin.taxes.store'),
            'method' => 'POST',
            'title' => 'Create Tax',
        ]);
    }

    public function store(TaxRequest $request): JsonResponse
    {
        Tax::create($request->validated());

        return response()->json(['message' => 'created'], Response::HTTP_CREATED);
    }

    public function edit(Tax $tax): View
    {
        return view('admin.taxes.partials.modal', [
            'mode' => 'edit',
            'tax' => $tax,
            'action' => route('admin.taxes.update', $tax),
            'method' => 'PUT',
            'title' => 'Edit Tax',
        ]);
    }

    public function update(TaxRequest $request, Tax $tax): JsonResponse
    {
        $tax->update($request->validated());

        return response()->json(['message' => 'updated'], Response::HTTP_OK);
    }

    public function destroy(Tax $tax): JsonResponse
    {
        $tax->delete();

        return response()->json(['message' => 'deleted'], Response::HTTP_OK);
    }

    public function massDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        Tax::whereIn('id', $validated['ids'])->delete();

        return response()->json(['message' => 'deleted'], Response::HTTP_OK);
    }
}
