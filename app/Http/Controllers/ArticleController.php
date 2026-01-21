<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('vatRate')
            ->latest()
            ->get();

        $vatRates = VatRate::select('id', 'name', 'rate')->orderBy('name')->get();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:articles,number',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'vat_rate_id' => 'nullable|exists:vat_rates,id',
            'photo' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // ✅ ASSOCIA O ARTIGO AO TENANT ATUAL
        $validated['tenant_id'] = config('app.current_tenant_id');

        Article::create($validated);

        return redirect()->back()->with('success', 'Artigo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return response()->json($article->load('vatRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50|unique:articles,number,'.$article->id,
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'vat_rate_id' => 'nullable|exists:vat_rates,id',
            'photo' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $article->update($validated);

        return redirect()->back()->with('success', 'Artigo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->back()->with('success', 'Artigo eliminado com sucesso!');
    }
}
