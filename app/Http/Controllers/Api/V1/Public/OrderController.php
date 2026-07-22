<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:150',
            'phone' => 'required|string',
            'email' => 'required|email|max:255',
            'address' => 'required|string|min:10|max:1000',
            'province' => 'required|string',
            'city' => 'required|string',
            'ticket_product_id' => 'required|exists:ticket_products,id',
            'quantity' => 'required|integer|min:1',
            'terms_accepted' => 'required|accepted',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $data,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json(['data' => $order, 'message' => 'Order berhasil dibuat!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function search(Request $request)
    {
        $request->validate(['search' => 'required|string|min:5']);

        $order = $this->orderService->searchOrder($request->search);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        // Mask sensitive data for public search
        return response()->json([
            'data' => [
                'order_code' => substr($order->order_code, 0, 8) . '****',
                'order_code_full' => $order->order_code,
                'order_status' => $order->order_status,
                'created_at' => $order->created_at,
                'expires_at' => $order->expires_at,
                'grand_total' => $order->grand_total,
                'customer_name' => $order->customer->name,
            ]
        ]);
    }

    public function show(string $orderCode)
    {
        $order = $this->orderService->getByCode($orderCode);
        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }
        return response()->json(['data' => $order]);
    }

    public function downloadETicket(\App\Models\OrderETicket $eTicket)
    {
        if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($eTicket->file_path)) {
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($eTicket->file_path)) {
                abort(404, 'File e-ticket tidak ditemukan.');
            }
            return \Illuminate\Support\Facades\Storage::disk('public')->download($eTicket->file_path, $eTicket->file_name);
        }

        return \Illuminate\Support\Facades\Storage::disk('private')->download($eTicket->file_path, $eTicket->file_name);
    }
}
