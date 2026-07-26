<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\{Event, TicketProduct, Faq, Province, City};
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function show()
    {
        $event = Event::where('status', 'published')->first();
        $ticketClosed = \App\Models\Setting::get('ticket_closed', '0');
        $ticketClosedMessage = \App\Models\Setting::get('ticket_closed_message', 'Mohon maaf, penjualan tiket saat ini sedang ditutup.');

        return response()->json([
            'data' => $event,
            'ticket_closed' => in_array($ticketClosed, ['1', 'true', true], true),
            'ticket_closed_message' => $ticketClosedMessage,
        ]);
    }

    public function ticketProducts()
    {
        $products = TicketProduct::where('is_active', true)
            ->whereIn('status', ['available', 'coming_soon', 'sold_out'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => array_merge($p->toArray(), [
                'effective_price' => $p->getEffectivePrice(),
                'available_quota' => $p->getAvailableQuota(),
            ]));

        return response()->json(['data' => $products]);
    }

    public function faqs()
    {
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();
        return response()->json(['data' => $faqs]);
    }

    public function provinces()
    {
        return response()->json(['data' => Province::orderBy('name')->get()]);
    }

    public function cities(Province $province)
    {
        return response()->json(['data' => $province->cities()->orderBy('name')->get()]);
    }
}
