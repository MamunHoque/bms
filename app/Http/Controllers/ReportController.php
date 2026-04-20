<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\UtilityReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function collection(Request $request)
    {
        $from = $request->input('from', Carbon::now()->subMonths(5)->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->endOfMonth()->toDateString());

        // Group payments by month — use correct date function per DB driver
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            $payments = Payment::whereBetween('paid_on', [$from, $to])
                ->select(DB::raw("strftime('%Y-%m', paid_on) as ym"), DB::raw('SUM(amount) as total'))
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();
        } else {
            $payments = Payment::whereBetween('paid_on', [$from, $to])
                ->select(DB::raw("DATE_FORMAT(paid_on, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();
        }

        // Payment method breakdown
        $methods = Payment::whereBetween('paid_on', [$from, $to])
            ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('method')
            ->get();

        $totalCollected = (float) Payment::whereBetween('paid_on', [$from, $to])->sum('amount');

        return view('reports.collection', compact('payments', 'methods', 'totalCollected', 'from', 'to'));
    }

    public function dues(Request $request)
    {
        $query = Invoice::with('lease.tenant', 'lease.unit.building')
            ->whereIn('status', ['unpaid', 'partial', 'overdue']);

        if ($request->filled('building_id')) {
            $query->whereHas('lease.unit', fn ($q) => $q->where('building_id', $request->building_id));
        }

        $dues = $query->orderBy('due_date')->get();
        $buildings = Building::orderBy('name')->get();

        $totalDue = $dues->sum(fn ($i) => $i->total - $i->paid_amount);

        // Bucket by age
        $now = Carbon::now();
        $buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
        foreach ($dues as $inv) {
            $due = (float) ($inv->total - $inv->paid_amount);
            if ($due <= 0) continue;
            $days = $inv->due_date->isFuture() ? 0 : (int) abs($inv->due_date->diffInDays($now));
            if ($days === 0) $buckets['current'] += $due;
            elseif ($days <= 30) $buckets['1_30'] += $due;
            elseif ($days <= 60) $buckets['31_60'] += $due;
            elseif ($days <= 90) $buckets['61_90'] += $due;
            else $buckets['over_90'] += $due;
        }

        return view('reports.dues', compact('dues', 'buildings', 'totalDue', 'buckets'));
    }

    public function occupancy()
    {
        $buildings = Building::withCount([
            'units',
            'units as occupied_count' => fn ($q) => $q->where('status', 'occupied'),
            'units as vacant_count' => fn ($q) => $q->where('status', 'vacant'),
            'units as maintenance_count' => fn ($q) => $q->where('status', 'maintenance'),
        ])->orderBy('name')->get();

        $totalUnits = Unit::count();
        $occupied = Unit::where('status', 'occupied')->count();
        $rate = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100, 1) : 0;

        return view('reports.occupancy', compact('buildings', 'totalUnits', 'occupied', 'rate'));
    }

    public function utilities(Request $request)
    {
        $from = $request->input('from', Carbon::now()->subMonths(5)->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->endOfMonth()->toDateString());

        $byType = UtilityReading::with('utilityType')
            ->whereBetween('period_month', [$from, $to])
            ->select('utility_type_id', DB::raw('SUM(consumption) as consumption'), DB::raw('SUM(amount) as amount'))
            ->groupBy('utility_type_id')
            ->get();

        // By month
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            $monthly = UtilityReading::whereBetween('period_month', [$from, $to])
                ->select(DB::raw("strftime('%Y-%m', period_month) as ym"), DB::raw('SUM(amount) as total'))
                ->groupBy('ym')->orderBy('ym')->get();
        } else {
            $monthly = UtilityReading::whereBetween('period_month', [$from, $to])
                ->select(DB::raw("DATE_FORMAT(period_month, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
                ->groupBy('ym')->orderBy('ym')->get();
        }

        return view('reports.utilities', compact('byType', 'monthly', 'from', 'to'));
    }
}
