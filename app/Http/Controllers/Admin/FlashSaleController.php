<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\FlashSalesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlashSaleRequest;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FlashSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FlashSalesDataTable $dataTable)
    {
        return $dataTable->render('admin.flashSale.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.flashSale.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FlashSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $productPayload = $data['products'] ?? [];
        unset($data['products']);

        $data['is_active'] = $request->boolean('is_active');

        $flashSale = DB::transaction(function () use ($data, $productPayload) {
            /** @var FlashSale $flashSale */
            $flashSale = FlashSale::create($data);
            $flashSale->products()->sync($this->formatProductsForSync($productPayload));

            return $flashSale;
        });

        audit_log(
            event: 'flash_sale_created',
            description: 'admin:create_flash_sale',
            before: null,
            after: $flashSale->toArray(),
            extra: ['products' => $productPayload],
            subjectId: $flashSale->id,
            subjectType: FlashSale::class
        );

        return redirect()
            ->route('admin.flash-sales.index')
            ->with('message', 'Flash sale created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FlashSale $flashSale): View
    {
        $flashSale->load('products');

        return view('admin.flashSale.edit', $this->formData($flashSale) + [
            'flashSale' => $flashSale,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FlashSaleRequest $request, FlashSale $flashSale): RedirectResponse
    {
        $data = $request->validated();
        $productPayload = $data['products'] ?? [];
        unset($data['products']);

        $data['is_active'] = $request->boolean('is_active');

        $before = $flashSale->toArray();
        $flashSale->load('products');

        DB::transaction(function () use ($flashSale, $data, $productPayload) {
            $flashSale->update($data);
            $flashSale->products()->sync($this->formatProductsForSync($productPayload, $flashSale));
        });

        $flashSale->refresh();

        audit_log(
            event: 'flash_sale_updated',
            description: 'admin:update_flash_sale',
            before: $before,
            after: $flashSale->toArray(),
            extra: ['products' => $productPayload],
            subjectId: $flashSale->id,
            subjectType: FlashSale::class
        );

        return redirect()
            ->route('admin.flash-sales.index')
            ->with('message', 'Flash sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FlashSale $flashSale): JsonResponse
    {
        $before = $flashSale->toArray();
        $flashSale->delete();

        audit_log(
            event: 'flash_sale_deleted',
            description: 'admin:delete_flash_sale',
            before: $before,
            after: null,
            extra: ['flash_sale_id' => $flashSale->id],
            subjectId: $flashSale->id,
            subjectType: FlashSale::class
        );

        return response()->json(['message' => 'Flash sale deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resources from storage.
     */
    public function massDestroy(FlashSaleRequest $request): JsonResponse
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        FlashSale::whereIn('id', $ids)->delete();

        audit_log(
            event: 'flash_sale_bulk_deleted',
            description: 'admin:bulk_delete_flash_sale',
            before: null,
            after: null,
            extra: ['ids' => $ids, 'count' => $count],
            subjectId: null,
            subjectType: FlashSale::class
        );

        return response()->json(['message' => 'Flash sales deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Shared data for create & edit screens.
     */
    private function formData(?FlashSale $flashSale = null): array
    {
        $productsList = Product::query()
            ->select(['id', 'name', 'price'])
            ->orderBy('name')
            ->get();

        $productRows = $flashSale
            ? $flashSale->products->map(function (Product $product) {
                return [
                    'product_id' => (string) $product->id,
                    'flash_price' => $product->pivot->flash_price,
                    'stock_flash' => $product->pivot->stock_flash,
                    'max_qty_per_user' => $product->pivot->max_qty_per_user,
                ];
            })->toArray()
            : [];

        return [
            'productsList' => $productsList,
            'productRows' => $productRows,
            'eventLists' => $this->eventOverview(),
        ];
    }

    /**
     * Map product payload into pivot-friendly array.
     *
     * @param  array<int, array<string, mixed>>  $products
     */
    private function formatProductsForSync(array $products, ?FlashSale $flashSale = null): array
    {
        $existingProducts = $flashSale
            ? $flashSale->products->keyBy('id')
            : collect();

        $result = [];
        foreach ($products as $product) {
            $productId = (int) $product['product_id'];
            /** @var \App\Models\Product|null $existing */
            $existing = $existingProducts instanceof Collection ? $existingProducts->get($productId) : null;

            $maxQty = $product['max_qty_per_user'] ?? 0;
            $result[$productId] = [
                'flash_price' => number_format((float) $product['flash_price'], 2, '.', ''),
                'stock_flash' => (int) $product['stock_flash'],
                'sold' => $existing ? $existing->pivot->sold : 0,
                'max_qty_per_user' => $maxQty === null || $maxQty === '' ? 0 : (int) $maxQty,
            ];
        }

        return $result;
    }

    /**
     * Build a snapshot of current events grouped by status.
     *
     * @return array<string, \Illuminate\Support\Collection<int, FlashSale>>
     */
    private function eventOverview(): array
    {
        $now = now();

        return [
            'scheduled' => FlashSale::query()
                ->where('start_at', '>', $now)
                ->orderBy('start_at')
                ->limit(5)
                ->get(),
            'running' => FlashSale::query()
                ->runningNow($now)
                ->orderBy('end_at')
                ->limit(5)
                ->get(),
            'finished' => FlashSale::query()
                ->where('end_at', '<', $now)
                ->orderByDesc('end_at')
                ->limit(5)
                ->get(),
        ];
    }
}
