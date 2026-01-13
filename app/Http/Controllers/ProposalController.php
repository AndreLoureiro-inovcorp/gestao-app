<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\CompanySetting;
use App\Models\Entity;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proposals = Proposal::with(['client', 'proposalArticles.article'])
            ->latest('created_at')
            ->get();

        return Inertia::render('Proposals/Index', [
            'proposals' => $proposals,
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

        return Inertia::render('Proposals/Create', [
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
            'validity_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,closed',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantity' => 'required|numeric|min:0.01',
            'articles.*.unit_price' => 'required|numeric|min:0',
            'articles.*.supplier_id' => 'nullable|exists:entities,id',
            'articles.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        $proposal = Proposal::create([
            'client_id' => $validated['client_id'],
            'validity_date' => $validated['validity_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        // Calcular total COM IVA
        $totalAmount = 0;

        foreach ($validated['articles'] as $articleData) {
            $article = Article::with('vatRate')->find($articleData['article_id']);
            $subtotal = $articleData['quantity'] * $articleData['unit_price'];

            $vatRate = $article->vatRate->rate ?? 0;
            $vatAmount = $subtotal * ($vatRate / 100);

            $lineTotal = $subtotal + $vatAmount;
            $totalAmount += $lineTotal;

            $proposal->proposalArticles()->create([
                'article_id' => $articleData['article_id'],
                'quantity' => $articleData['quantity'],
                'unit_price' => $articleData['unit_price'],
                'supplier_id' => $articleData['supplier_id'] ?? null,
                'cost_price' => $articleData['cost_price'] ?? null,
            ]);
        }

        $proposal->update(['total_amount' => $totalAmount]);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Proposal $proposal)
    {
        $proposal->load(['client', 'proposalArticles.article', 'proposalArticles.supplier']);

        return Inertia::render('Proposals/Show', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proposal $proposal)
    {
        $proposal->load(['proposalArticles.article.vatRate']);

        $clients = Entity::whereJsonContains('type', 'client')->orderBy('name')->get(['id', 'name']);

        $articles = Article::with('vatRate')->where('status', 'active')->orderBy('name')->get();

        $suppliers = Entity::whereJsonContains('type', 'supplier')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Proposals/Edit', [
            'proposal' => $proposal,
            'clients' => $clients,
            'articles' => $articles,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:entities,id',
            'validity_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,closed',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantity' => 'required|numeric|min:0.01',
            'articles.*.unit_price' => 'required|numeric|min:0',
            'articles.*.supplier_id' => 'nullable|exists:entities,id',
            'articles.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        $proposal->update([
            'client_id' => $validated['client_id'],
            'validity_date' => $validated['validity_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        $proposal->proposalArticles()->delete();

        $totalAmount = 0;

        foreach ($validated['articles'] as $articleData) {
            $article = Article::with('vatRate')->find($articleData['article_id']);

            $subtotal = $articleData['quantity'] * $articleData['unit_price'];

            $vatRate = $article->vatRate->rate ?? 0;
            $vatAmount = $subtotal * ($vatRate / 100);

            $lineTotal = $subtotal + $vatAmount;
            $totalAmount += $lineTotal;

            $proposal->proposalArticles()->create([
                'article_id' => $articleData['article_id'],
                'quantity' => $articleData['quantity'],
                'unit_price' => $articleData['unit_price'],
                'supplier_id' => $articleData['supplier_id'] ?? null,
                'cost_price' => $articleData['cost_price'] ?? null,
            ]);
        }

        $proposal->update(['total_amount' => $totalAmount]);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return redirect()->back()
            ->with('success', 'Proposta eliminada com sucesso!');
    }

    /**
     * Download proposal as PDF
     */
    public function downloadPdf(Proposal $proposal)
    {
        $proposal->load(['client', 'proposalArticles.article.vatRate']);
        $companySetting = CompanySetting::first();

        $pdf = Pdf::loadView('pdfs.proposal', [
            'proposal' => $proposal,
            'companySetting' => $companySetting,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("Proposta-{$proposal->number}.pdf");
    }
}
