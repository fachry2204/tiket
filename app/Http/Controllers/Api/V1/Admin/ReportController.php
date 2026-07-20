<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Ticket};
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from = $request->date_from ?? now()->subDays(30)->toDateString();
        $to = $request->date_to ?? today()->toDateString();

        return response()->json(['data' => [
            'summary' => [
                'total_orders' => Order::whereBetween('created_at', [$from, $to])->count(),
                'paid_orders' => Order::where('order_status','paid')->whereBetween('created_at', [$from, $to])->count(),
                'total_revenue' => Order::where('order_status','paid')->whereBetween('paid_at', [$from, $to])->sum('grand_total'),
                'total_tickets' => Ticket::whereBetween('issued_at', [$from, $to])->count(),
            ],
            'daily' => Order::where('order_status','paid')
                ->whereBetween('paid_at', [$from, $to])
                ->selectRaw('DATE(paid_at) as date, COUNT(*) as count, SUM(grand_total) as revenue')
                ->groupBy('date')->orderBy('date')->get(),
            'by_province' => Order::where('order_status','paid')
                ->join('customers','orders.customer_id','=','customers.id')
                ->selectRaw('customers.province, COUNT(*) as count')
                ->groupBy('customers.province')->orderByDesc('count')->get(),
        ]]);
    }

    public function auditLogs(Request $request)
    {
        $logs = \App\Models\AuditLog::with('user')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->latest()->paginate(50);
        return response()->json($logs);
    }
}
