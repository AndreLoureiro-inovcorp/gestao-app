<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Entity;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::with(['client', 'proposalArticles.article'])
            ->latest('created_at')
            ->get();

        return Inertia::render('Proposals/Index', [
            'proposals' => $proposals,
        ]);
    }

    public function create()
    {
        $clients = Entity::whereJsonContains('type', 'client')
            ->orderBy('name')
            ->get(['id', 'name']);

        $articles = Article::where('status', 'active')
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

        // Criar proposta
        $proposal = Proposal::create([
            'client_id' => $validated['client_id'],
            'validity_date' => $validated['validity_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        // Adicionar artigos
        $totalAmount = 0;

        foreach ($validated['articles'] as $articleData) {
            $subtotal = $articleData['quantity'] * $articleData['unit_price'];
            $totalAmount += $subtotal;

            $proposal->proposalArticles()->create([
                'article_id' => $articleData['article_id'],
                'quantity' => $articleData['quantity'],
                'unit_price' => $articleData['unit_price'],
                'supplier_id' => $articleData['supplier_id'] ?? null,
                'cost_price' => $articleData['cost_price'] ?? null,
            ]);
        }

        // Atualizar total
        $proposal->update(['total_amount' => $totalAmount]);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta criada com sucesso!');
    }

    public function show(Proposal $proposal)
    {
        $proposal->load(['client', 'proposalArticles.article', 'proposalArticles.supplier']);

        return Inertia::render('Proposals/Show', [
            'proposal' => $proposal,
        ]);
    }

    public function edit(Proposal $proposal)
    {
        $proposal->load(['proposalArticles.article']);

        $clients = Entity::whereJsonContains('type', 'client')
            ->orderBy('name')
            ->get(['id', 'name']);

        $articles = Article::where('status', 'active')
            ->orderBy('name')
            ->get();

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

        // Atualizar proposta
        $proposal->update([
            'client_id' => $validated['client_id'],
            'validity_date' => $validated['validity_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        // Remover artigos antigos e adicionar novos
        $proposal->proposalArticles()->delete();

        $totalAmount = 0;

        foreach ($validated['articles'] as $articleData) {
            $subtotal = $articleData['quantity'] * $articleData['unit_price'];
            $totalAmount += $subtotal;

            $proposal->proposalArticles()->create([
                'article_id' => $articleData['article_id'],
                'quantity' => $articleData['quantity'],
                'unit_price' => $articleData['unit_price'],
                'supplier_id' => $articleData['supplier_id'] ?? null,
                'cost_price' => $articleData['cost_price'] ?? null,
            ]);
        }

        // Atualizar total
        $proposal->update(['total_amount' => $totalAmount]);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return redirect()->back()
            ->with('success', 'Proposta eliminada com sucesso!');
    }
}
