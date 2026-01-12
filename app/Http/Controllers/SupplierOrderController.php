<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierOrderController extends Controller
{
    public function index()
    {
        $orders = SupplierOrder::with(['supplier', 'clientOrder', 'supplierOrderArticles.article'])
            ->latest('created_at')
            ->get();

        return Inertia::render('SupplierOrders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(SupplierOrder $supplierOrder)
    {
        $supplierOrder->load(['supplier', 'clientOrder', 'supplierOrderArticles.article']);

        return Inertia::render('SupplierOrders/Show', [
            'order' => $supplierOrder,
        ]);
    }

    public function update(Request $request, SupplierOrder $supplierOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,closed',
            'notes' => 'nullable|string',
        ]);

        $supplierOrder->update($validated);

        return redirect()->back()
            ->with('success', 'Encomenda atualizada com sucesso!');
    }

    public function destroy(SupplierOrder $supplierOrder)
    {
        $supplierOrder->delete();

        return redirect()->back()
            ->with('success', 'Encomenda eliminada com sucesso!');
    }
}
