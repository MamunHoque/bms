<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('invoice.lease.tenant', 'invoice.lease.unit.building');

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('from')) {
            $query->where('paid_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('paid_on', '<=', $request->to);
        }

        $payments = $query->latest('paid_on')->paginate(20)->withQueryString();
        $total = (float) $query->sum('amount');

        return view('payments.index', compact('payments', 'total'));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::with('lease.tenant', 'lease.unit.building')
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        $preselect = $request->invoice_id;
        return view('payments.create', compact('invoices', 'preselect'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date'],
            'method' => ['required', 'in:cash,bkash,nagad,bank,card,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $payment = Payment::create($data);
            $invoice = $payment->invoice;
            $invoice->paid_amount = (float) $invoice->paid_amount + (float) $payment->amount;
            $invoice->save();
            $invoice->recalculateStatus();
        });

        return redirect()->route('payments.index')->with('status', 'Payment recorded.');
    }

    public function show(Payment $payment)
    {
        $payment->load('invoice.lease.tenant');
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date'],
            'method' => ['required', 'in:cash,bkash,nagad,bank,card,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($payment, $data) {
            $invoice = $payment->invoice;
            $invoice->paid_amount = (float) $invoice->paid_amount - (float) $payment->amount + (float) $data['amount'];
            $payment->update($data);
            $invoice->save();
            $invoice->recalculateStatus();
        });

        return redirect()->route('payments.index')->with('status', 'Payment updated.');
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $invoice->paid_amount = (float) $invoice->paid_amount - (float) $payment->amount;
            $payment->delete();
            $invoice->save();
            $invoice->recalculateStatus();
        });

        return redirect()->route('payments.index')->with('status', 'Payment removed.');
    }
}
