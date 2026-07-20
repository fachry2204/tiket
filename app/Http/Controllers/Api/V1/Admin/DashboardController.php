<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Ticket, PaymentConfirmation};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        return response()->json([
            'data' => [
                'total_orders' => Order::count(),
                'orders_today' => Order::whereDate('created_at', $today)->count(),
                'pending_payment' => Order::where('order_status', 'pending_payment')->count(),
                'waiting_verification' => Order::where('order_status', 'waiting_verification')->count(),
                'paid' => Order::where('order_status', 'paid')->count(),
                'payment_rejected' => Order::where('order_status', 'payment_rejected')->count(),
                'expired' => Order::where('order_status', 'expired')->count(),
                'total_tickets_sold' => Ticket::where('status', 'active')->count() + Ticket::where('status', 'used')->count(),
                'total_checkins' => Ticket::where('status', 'used')->count(),
                'total_revenue' => Order::where('order_status', 'paid')->sum('grand_total'),
                'recent_orders' => Order::with('customer')->latest()->limit(10)->get(),
                'pending_payments' => PaymentConfirmation::with('order.customer')
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(10)
                    ->get(),
                'daily_sales' => Order::where('order_status', 'paid')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(grand_total) as revenue')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ]
        ]);
    }
}
