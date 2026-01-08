<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'article_id',
        'supplier_id',
        'quantity',
        'unit_price',
        'cost_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate subtotal.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($proposalArticle) {
            $proposalArticle->subtotal = $proposalArticle->quantity * $proposalArticle->unit_price;
        });
    }

    /**
     * Relationships
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }
}
