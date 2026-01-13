<?php

namespace App\Http\Controllers;

use App\Mail\SupplierPaymentNotification;
use App\Models\Entity;
use App\Models\SupplierInvoice;
use App\Models\SupplierOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SupplierInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = SupplierInvoice::with(['supplier', 'supplierOrder'])
            ->latest('invoice_date')
            ->get();

        return Inertia::render('SupplierInvoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Entity::whereJsonContains('type', 'supplier')
            ->orderBy('name')
            ->get(['id', 'name']);

        $supplierOrders = SupplierOrder::with('supplier')
            ->whereDoesntHave('supplierInvoices')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SupplierInvoices/Create', [
            'suppliers' => $suppliers,
            'supplierOrders' => $supplierOrders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'supplier_id' => 'required|exists:entities,id',
            'supplier_order_id' => 'nullable|exists:supplier_orders,id',
            'total_amount' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'status' => 'required|in:pending,paid',
        ]);

        $year = date('Y');
        $lastNumber = SupplierInvoice::where('number', 'like', "FATF-{$year}-%")
            ->lockForUpdate()
            ->max('number');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, -3)) + 1 : 1;
        $number = sprintf('FATF-%s-%03d', $year, $nextNumber);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('supplier-invoices', 'public');
        }

        SupplierInvoice::create([
            'number' => $number,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'supplier_id' => $validated['supplier_id'],
            'supplier_order_id' => $validated['supplier_order_id'],
            'total_amount' => $validated['total_amount'],
            'document_path' => $documentPath,
            'status' => $validated['status'],
        ]);

        return redirect()->route('supplier-invoices.index')
            ->with('success', 'Fatura criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierInvoice $supplierInvoice)
    {
        $supplierInvoice->load(['supplier', 'supplierOrder']);

        return Inertia::render('SupplierInvoices/Show', [
            'invoice' => $supplierInvoice,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierInvoice $supplierInvoice)
    {
        if ($supplierInvoice->document_path) {
            Storage::disk('public')->delete($supplierInvoice->document_path);
        }
        if ($supplierInvoice->payment_proof_path) {
            Storage::disk('public')->delete($supplierInvoice->payment_proof_path);
        }

        $supplierInvoice->delete();

        return redirect()->back()
            ->with('success', 'Fatura eliminada com sucesso!');
    }

    /**
     * Send payment notification via email with proof attachment
     */
    public function sendPaymentNotification(Request $request, SupplierInvoice $supplierInvoice)
    {
        $supplierInvoice->load('supplier');

        if (! $supplierInvoice->supplier->email) {
            return redirect()->back()
                ->with('error', 'Fornecedor não tem email registado!');
        }

        $request->validate([
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($supplierInvoice->payment_proof_path) {
            Storage::disk('public')->delete($supplierInvoice->payment_proof_path);
        }

        $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

        $supplierInvoice->update([
            'payment_proof_path' => $paymentProofPath,
        ]);

        Mail::to($supplierInvoice->supplier->email)
            ->send(new SupplierPaymentNotification($supplierInvoice));

        return redirect()->back()
            ->with('success', 'Comprovativo enviado com sucesso!');
    }
}
