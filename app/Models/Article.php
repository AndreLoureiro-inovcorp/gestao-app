<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Article extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'reference',
        'name',
        'description',
        'price',
        'vat_rate_id',
        'photo',
        'notes',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['reference', 'name', 'price', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function vatRate()
    {
        return $this->belongsTo(VatRate::class);
    }

    /**
     * Get all proposals that include this article.
     */
    public function proposals()
    {
        return $this->belongsToMany(Proposal::class, 'proposal_articles')
            ->withPivot(['quantity', 'unit_price', 'cost_price', 'subtotal', 'supplier_id'])
            ->withTimestamps();
    }

    /**
     * Get price with VAT applied
     */
    public function getPriceWithVatAttribute()
    {
        $vatRate = $this->vatRate->rate ?? 0;

        return $this->price * (1 + ($vatRate / 100));
    }

    /**
     * Get all client orders that include this article.
     */
    public function clientOrders()
    {
        return $this->belongsToMany(ClientOrder::class, 'client_order_articles')
            ->withPivot(['quantity', 'unit_price', 'cost_price', 'subtotal', 'supplier_id'])
            ->withTimestamps();
    }

    /**
     * Get all supplier orders that include this article.
     */
    public function supplierOrders()
    {
        return $this->belongsToMany(SupplierOrder::class, 'supplier_order_articles')
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->withTimestamps();
    }
}
