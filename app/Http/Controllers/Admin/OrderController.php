<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrdersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipping;
use App\Services\RajaOngkirService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(OrdersDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.orders.index');
    }

    /**
     * Show the specified order.
     */
    public function show(Order $order): View
    {
        $order->load(
            'user',
            'items',
            'items.product',
            'invoice',
            'invoice.payment',
            'invoice.payment.bank',
            'shipping'
        );

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Confirm payment for the specified order.
     */
    public function confirmPayment(Request $request, Order $order): RedirectResponse
    {
        $validatedData = $request->validate([
            'action' => [
                'required',
                'string',
                'in:accept,reject',
            ],
            'cancel_reason' => [
                'nullable',
                'string',
                'min:1',
                'max:100',
            ],
        ]);

        DB::transaction(function () use ($validatedData, $order) {
            if ($validatedData['action'] === 'accept') {
                $order->update([
                    'status' => Order::STATUS_PROCESSING,
                    'confirmed_at' => now(),
                ]);
                $order->invoice->update(['status' => Invoice::STATUS_PAID]);
                $order->invoice->payment->update(['status' => Payment::STATUS_RELEASED]);
            }

            if ($validatedData['action'] === 'reject') {
                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'cancel_reason' => $validatedData['cancel_reason'],
                ]);
                $order->invoice->update(['status' => Invoice::STATUS_UNPAID]);
                $order->invoice->payment->update(['status' => Payment::STATUS_CANCELLED]);
                $order->items->each(fn($item) => $item->product->increment('stock', $item->quantity));
            }
        }, 3);

        toastr('Payment confirmed successfully.', 'success');

        return back();
    }

    /**
     * Confirm shipping for the specified order.
     */
    public function confirmShipping(Request $request, Order $order): RedirectResponse
    {
        $validatedData = $request->validate([
            'tracking_number' => [
                'required',
                'string',
                'min:1',
                'max:50',
            ],
        ]);

        DB::transaction(function () use ($order, $validatedData) {
            $order->update(['status' => Order::STATUS_ON_DELIVERY]);
            $order->shipping->update([
                'status' => Shipping::STATUS_PROCESSING,
                'tracking_number' => $validatedData['tracking_number'],
            ]);
        }, 3);

        toastr('Shipping confirmed successfully.', 'success');

        return back();
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Remove the specified order(s) from storage.
     *
     * @return JsonResponse
     */
    public function massDestroy(OrderRequest $request)
    {
        $ids = $request->validated('ids');
        $count = count($ids);

        Order::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_orders',
            before: null,
            after: null,
            extra: [
                'changed' => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' orders.'],
            ],
            subjectId: null,
            subjectType: \App\Models\Order::class
        );

        return response()->json(['message' => 'Orders deleted successfully.'], Response::HTTP_OK);
    }

    /**
     * Generate a PDF invoice for the specified orders.
     */
    public function generateInvoicePdf(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'numeric',
                'exists:orders,id',
            ],
        ]);

        $orders = Order::whereIn('id', $request->ids)->get();

        $html = '';

        foreach ($orders as $order) {
            $html .= view('invoice', compact('order'))->render();
            // $html .= '<div style="page-break-after: always;"></div>';
        }

        $pdf = Pdf::loadHTML($html)->setPaper('a5', 'potrait');
        $base64Pdf = base64_encode($pdf->output());

        $filename = $orders->count() > 1
            ? $orders->map(fn($order) => $order->invoice->number)->join('_') . '.pdf'
            : $orders->first()->invoice->number . '.pdf';

        return response()->json(['data' => $base64Pdf, 'filename' => $filename], Response::HTTP_OK);
    }

    public function trackWaybill(Request $request, Order $order): JsonResponse
    {
        $courierCode = (string) $order->shipping->courier;
        $waybillNumber = (string) $order->shipping->tracking_number;

        // RajaOngkir premium may require last 5 digits of receiver phone number
        $rawPhone = $order->shipping->address['phone'] ?? null;
        $lastPhoneNumber = null;
        if (is_string($rawPhone) && $rawPhone !== '') {
            $digitsOnly = preg_replace('/\D+/', '', $rawPhone) ?? '';
            $lastPhoneNumber = strlen($digitsOnly) >= 5 ? substr($digitsOnly, -5) : null;
        }

        $service = new RajaOngkirService;

        try {
            $trackingData = $service->trackWaybill($courierCode, $waybillNumber, $lastPhoneNumber);

            if ($trackingData === null) {
                return response()->json([
                    'meta' => [
                        'message' => 'Tidak dapat mengambil data tracking.',
                        'code' => 500,
                        'status' => 'error',
                    ],
                    'html' => view('admin.orders.partials.tracking-waybill', [
                        'trackingData' => [],
                    ])->render(),
                    'data' => [],
                ], Response::HTTP_OK);
            }

            $html = view('admin.orders.partials.tracking-waybill', [
                'trackingData' => $trackingData,
            ])->render();

            return response()->json([
                'meta' => [
                    'message' => 'OK',
                    'code' => 200,
                    'status' => 'success',
                ],
                'html' => $html,
                'data' => $trackingData,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json([
                'meta' => [
                    'message' => 'Gagal mengambil data tracking: ' . $e->getMessage(),
                    'code' => 500,
                    'status' => 'error',
                ],
                'html' => view('admin.orders.partials.tracking-waybill', [
                    'trackingData' => [],
                ])->render(),
                'data' => [],
            ], Response::HTTP_OK);
        }
    }
}
