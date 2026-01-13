<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ClientOrder;
use App\Models\CompanySetting;
use App\Models\Entity;
use App\Models\Proposal;
use App\Models\SupplierOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = ClientOrder::with(['client', 'proposal', 'clientOrderArticles.article'])
            ->latest('created_at')
            ->get();

        return Inertia::render('ClientOrders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Entity::whereJsonContains('type', 'client')
            ->orderBy('name')
            ->get(['id', 'name']);

        $articles = Article::with('vatRate')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $suppliers = Entity::whereJsonContains('type', 'supplier')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('ClientOrders/Create', [
            'clients' => $clients,
            'articles' => $articles,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:entities,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,closed',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantity' => 'required|numeric|min:0.01',
            'articles.*.unit_price' => 'required|numeric|min:0',
            'articles.*.supplier_id' => 'nullable|exists:entities,id',
            'articles.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $order = ClientOrder::create([
                'client_id' => $validated['client_id'],
                'proposal_id' => $validated['proposal_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ]);

            $totalAmount = 0;

            foreach ($validated['articles'] as $articleData) {
                $article = Article::with('vatRate')->find($articleData['article_id']);

                $subtotal = $articleData['quantity'] * $articleData['unit_price'];

                $vatRate = $article->vatRate->rate ?? 0;
                $vatAmount = $subtotal * ($vatRate / 100);

                $lineTotal = $subtotal + $vatAmount;
                $totalAmount += $lineTotal;

                $order->clientOrderArticles()->create([
                    'article_id' => $articleData['article_id'],
                    'quantity' => $articleData['quantity'],
                    'unit_price' => $articleData['unit_price'],
                    'supplier_id' => $articleData['supplier_id'] ?? null,
                    'cost_price' => $articleData['cost_price'] ?? null,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('client-orders.index')
            ->with('success', 'Encomenda criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientOrder $clientOrder)
    {
        $clientOrder->load(['client', 'proposal', 'clientOrderArticles.article', 'clientOrderArticles.supplier']);

        return Inertia::render('ClientOrders/Show', [
            'order' => $clientOrder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientOrder $clientOrder)
    {
        $clientOrder->load(['clientOrderArticles.article.vatRate']);

        $clients = Entity::whereJsonContains('type', 'client')
            ->orderBy('name')
            ->get(['id', 'name']);

        $articles = Article::with('vatRate')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $suppliers = Entity::whereJsonContains('type', 'supplier')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('ClientOrders/Edit', [
            'order' => $clientOrder,
            'clients' => $clients,
            'articles' => $articles,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientOrder $clientOrder)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:entities,id',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,closed',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantity' => 'required|numeric|min:0.01',
            'articles.*.unit_price' => 'required|numeric|min:0',
            'articles.*.supplier_id' => 'nullable|exists:entities,id',
            'articles.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $clientOrder) {
            $clientOrder->update([
                'client_id' => $validated['client_id'],
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ]);

            $clientOrder->clientOrderArticles()->delete();

            $totalAmount = 0;

            foreach ($validated['articles'] as $articleData) {

                $article = Article::with('vatRate')->find($articleData['article_id']);

                $subtotal = $articleData['quantity'] * $articleData['unit_price'];

                $vatRate = $article->vatRate->rate ?? 0;
                $vatAmount = $subtotal * ($vatRate / 100);

                $lineTotal = $subtotal + $vatAmount;
                $totalAmount += $lineTotal;

                $clientOrder->clientOrderArticles()->create([
                    'article_id' => $articleData['article_id'],
                    'quantity' => $articleData['quantity'],
                    'unit_price' => $articleData['unit_price'],
                    'supplier_id' => $articleData['supplier_id'] ?? null,
                    'cost_price' => $articleData['cost_price'] ?? null,
                ]);
            }

            $clientOrder->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('client-orders.index')
            ->with('success', 'Encomenda atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientOrder $clientOrder)
    {
        $clientOrder->delete();

        return redirect()->back()
            ->with('success', 'Encomenda eliminada com sucesso!');
    }

    /**
     * Converter proposta em encomenda
     */
    public function createFromProposal(Proposal $proposal)
    {
        if ($proposal->status !== 'closed') {
            return redirect()->back()
                ->with('error', 'Só é possível converter propostas fechadas!');
        }

        if ($proposal->clientOrder) {
            return redirect()->route('client-orders.edit', $proposal->clientOrder)
                ->with('info', 'Esta proposta já foi convertida em encomenda.');
        }

        DB::transaction(function () use ($proposal) {
            $order = ClientOrder::create([
                'client_id' => $proposal->client_id,
                'proposal_id' => $proposal->id,
                'notes' => $proposal->notes,
                'status' => 'draft',
                'total_amount' => $proposal->total_amount,
            ]);

            foreach ($proposal->proposalArticles as $proposalArticle) {
                $order->clientOrderArticles()->create([
                    'article_id' => $proposalArticle->article_id,
                    'quantity' => $proposalArticle->quantity,
                    'unit_price' => $proposalArticle->unit_price,
                    'supplier_id' => $proposalArticle->supplier_id,
                    'cost_price' => $proposalArticle->cost_price,
                ]);
            }
        });

        return redirect()->route('client-orders.index')
            ->with('success', 'Proposta convertida em encomenda com sucesso!');
    }

    /**
     * Criar encomendas de fornecedor a partir de encomenda de cliente
     */
    public function createSupplierOrders(ClientOrder $clientOrder)
    {
        if ($clientOrder->status !== 'closed') {
            return redirect()->back()
                ->with('error', 'Só é possível criar encomendas de fornecedor para encomendas fechadas!');
        }

        $articlesBySupplier = $clientOrder->clientOrderArticles()
            ->with('article.vatRate')
            ->whereNotNull('supplier_id')
            ->get()
            ->groupBy('supplier_id');

        if ($articlesBySupplier->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Nenhum artigo tem fornecedor associado!');
        }

        DB::transaction(function () use ($articlesBySupplier, $clientOrder) {
            foreach ($articlesBySupplier as $supplierId => $articles) {
                $totalAmount = $articles->sum(function ($article) {
                    $price = $article->cost_price ?? $article->unit_price;
                    $subtotal = $article->quantity * $price;

                    $vatRate = $article->article->vatRate->rate ?? 0;
                    $vatAmount = $subtotal * ($vatRate / 100);

                    return $subtotal + $vatAmount;
                });

                $supplierOrder = SupplierOrder::create([
                    'supplier_id' => $supplierId,
                    'client_order_id' => $clientOrder->id,
                    'status' => 'draft',
                    'total_amount' => $totalAmount,
                    'notes' => "Encomenda gerada automaticamente a partir de ENC {$clientOrder->number}",
                ]);

                foreach ($articles as $article) {
                    $supplierOrder->supplierOrderArticles()->create([
                        'article_id' => $article->article_id,
                        'quantity' => $article->quantity,
                        'unit_price' => $article->cost_price ?? $article->unit_price,
                    ]);
                }
            }
        });

        return redirect()->route('supplier-orders.index')
            ->with('success', 'Encomendas de fornecedor criadas com sucesso!');
    }

    /**
     * Download client order as PDF
     */
    public function downloadPdf(ClientOrder $clientOrder)
    {
        $clientOrder->load(['client', 'clientOrderArticles.article.vatRate']);

        $companySetting = CompanySetting::first();

        $pdf = Pdf::loadView('pdfs.client-order', [
            'order' => $clientOrder,
            'companySetting' => $companySetting,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Encomenda-{$clientOrder->number}.pdf");
    }
}
