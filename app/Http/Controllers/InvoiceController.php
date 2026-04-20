<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lease;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('lease.tenant', 'lease.unit.building');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->whereDate('period_month', Carbon::parse($request->month)->startOfMonth()->toDateString());
        }

        // Auto-mark overdue before listing
        Invoice::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $invoices = $query->latest('issue_date')->paginate(20)->withQueryString();

        $summary = [
            'total' => (float) Invoice::sum('total'),
            'collected' => (float) Invoice::sum('paid_amount'),
            'outstanding' => (float) Invoice::selectRaw('SUM(total - paid_amount) as d')->value('d'),
        ];

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create()
    {
        $leases = Lease::with('tenant', 'unit.building')->where('status', 'active')->get();
        return view('invoices.create', compact('leases'));
    }

    public function store(Request $request, InvoiceService $service)
    {
        $data = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'period_month' => ['required', 'date'],
        ]);

        $lease = Lease::findOrFail($data['lease_id']);
        $invoice = $service->generateForLease($lease, Carbon::parse($data['period_month']));

        if (!$invoice) {
            return back()->withErrors(['lease_id' => 'Could not create invoice. Lease may not be active.']);
        }

        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('lease.tenant', 'lease.unit.building', 'items', 'payments');
        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('lease.tenant', 'lease.unit.building', 'items', 'payments');
        return view('invoices.print', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'due_date' => ['required', 'date'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['late_fee'] = $data['late_fee'] ?? 0;
        $invoice->update($data);
        // Recompute total
        $invoice->update([
            'total' => $invoice->subtotal + $invoice->utility_total + $invoice->late_fee,
        ]);
        $invoice->recalculateStatus();
        return redirect()->route('invoices.show', $invoice)->with('status', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('status', 'Invoice deleted.');
    }

    public function generateMonthly(Request $request, InvoiceService $service)
    {
        $data = $request->validate([
            'period_month' => ['required', 'date'],
        ]);
        $result = $service->generateForMonth(Carbon::parse($data['period_month']));
        return redirect()->route('invoices.index')->with('status',
            "Generated {$result['created']} invoice(s). {$result['skipped']} already existed.");
    }
}
