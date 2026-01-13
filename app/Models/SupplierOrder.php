<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'order_date',
        'supplier_id',
        'client_order_id',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (! $order->number) {
                $year = date('Y');

                $lastNumber = self::where('number', 'like', "ENCF-{$year}-%")
                    ->lockForUpdate()
                    ->max('number');

                $nextNumber = $lastNumber ? intval(substr($lastNumber, -3)) + 1 : 1;
                $order->number = sprintf('ENCF-%s-%03d', $year, $nextNumber);
            }

            if (! $order->order_date) {
                $order->order_date = Carbon::now();
            }
        });
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'supplier_id', 'status', 'total_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function clientOrder()
    {
        return $this->belongsTo(ClientOrder::class);
    }

    public function supplierOrderArticles()
    {
        return $this->hasMany(SupplierOrderArticle::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'supplier_order_articles')
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->withTimestamps();
    }

    public function supplierInvoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }
}
