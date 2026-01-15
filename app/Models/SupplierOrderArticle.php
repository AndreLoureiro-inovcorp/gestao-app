<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class SupplierOrderArticle extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'supplier_order_id',
        'article_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate subtotal.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($orderArticle) {
            $orderArticle->subtotal = $orderArticle->quantity * $orderArticle->unit_price;
        });
    }

    /**
     * Relationships
     */
    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
