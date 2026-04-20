<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();

        // KPI cards
        $totalBuildings = Building::count();
        $totalUnits = Unit::count();
        $occupiedUnits = Unit::where('status', 'occupied')->count();
        $vacantUnits = Unit::where('status', 'vacant')->count();
        $totalTenants = Tenant::count();
        $activeLeases = Lease::where('status', 'active')->count();

        $monthInvoiced = (float) Invoice::whereDate('period_month', $thisMonth->toDateString())->sum('total');
        $monthCollected = (float) Payment::whereHas('invoice', function ($q) use ($thisMonth) {
            $q->whereDate('period_month', $thisMonth->toDateString());
        })->sum('amount');
        $monthDue = max(0, $monthInvoiced - $monthCollected);

        $totalOutstanding = (float) Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');

        $overdueCount = Invoice::where('due_date', '<', $now->toDateString())
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        // Monthly invoiced vs collected — last 6 months
        $labels = [];
        $invoicedSeries = [];
        $collectedSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i)->startOfMonth();
            $labels[] = $m->format('M Y');
            $invoicedSeries[] = (float) Invoice::whereDate('period_month', $m->toDateString())->sum('total');
            $collectedSeries[] = (float) Payment::whereHas('invoice', function ($q) use ($m) {
                $q->whereDate('period_month', $m->toDateString());
            })->sum('amount');
        }

        // Payment method breakdown — last 30 days
        $methodBreakdown = Payment::where('paid_on', '>=', $now->copy()->subDays(30)->toDateString())
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        // Status distribution
        $statusCounts = Invoice::select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        // Recent invoices and payments
        $recentInvoices = Invoice::with('lease.tenant', 'lease.unit.building')
            ->latest()->take(5)->get();
        $recentPayments = Payment::with('invoice.lease.tenant')
            ->latest('paid_on')->take(5)->get();

        // Overdue invoices list
        $overdueInvoices = Invoice::with('lease.tenant', 'lease.unit.building')
            ->where('due_date', '<', $now->toDateString())
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Occupancy rate
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        // Collection rate this month
        $collectionRate = $monthInvoiced > 0 ? round(($monthCollected / $monthInvoiced) * 100, 1) : 0;

        return view('dashboard.index', compact(
            'totalBuildings', 'totalUnits', 'occupiedUnits', 'vacantUnits',
            'totalTenants', 'activeLeases',
            'monthInvoiced', 'monthCollected', 'monthDue',
            'totalOutstanding', 'overdueCount',
            'labels', 'invoicedSeries', 'collectedSeries',
            'methodBreakdown', 'statusCounts',
            'recentInvoices', 'recentPayments', 'overdueInvoices',
            'occupancyRate', 'collectionRate'
        ));
    }
}
