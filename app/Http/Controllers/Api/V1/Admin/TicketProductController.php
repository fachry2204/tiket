<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TicketProduct, Event};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketProductController extends Controller
{
    public function index()
    {
        return response()->json(['data' => TicketProduct::with('event')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'valid_date' => 'nullable|date',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_start_at' => 'nullable|date',
            'promo_end_at' => 'nullable|date|after:promo_start_at',
            'quota' => 'required|integer|min:1',
            'max_per_order' => 'required|integer|min:1',
            'admin_fee' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,coming_soon,available,sold_out,closed',
            'is_active' => 'boolean',
            'is_special' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $data['slug'] = Str::slug($data['name'] . '-' . time());
        $product = TicketProduct::create($data);
        return response()->json(['data' => $product, 'message' => 'Produk tiket berhasil dibuat.'], 201);
    }

    public function show(TicketProduct $ticketProduct)
    {
        return response()->json(['data' => $ticketProduct->load('event')]);
    }

    public function update(Request $request, TicketProduct $ticketProduct)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_start_at' => 'nullable|date',
            'promo_end_at' => 'nullable|date',
            'quota' => 'sometimes|integer|min:1',
            'max_per_order' => 'sometimes|integer|min:1',
            'admin_fee' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:draft,coming_soon,available,sold_out,closed',
            'is_active' => 'boolean',
            'is_special' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $ticketProduct->update($data);
        return response()->json(['data' => $ticketProduct, 'message' => 'Produk tiket diperbarui.']);
    }

    public function destroy(TicketProduct $ticketProduct)
    {
        $ticketProduct->delete();
        return response()->json(['message' => 'Produk tiket dihapus.']);
    }
}
