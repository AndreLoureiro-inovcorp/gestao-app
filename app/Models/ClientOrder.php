<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\BelongsToTenant;

class ClientOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'number',
        'order_date',
        'client_id',
        'proposal_id',
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
                $lastNumber = self::where('number', 'like', "ENC-{$year}-%")->max('number');
                $nextNumber = $lastNumber ? intval(substr($lastNumber, -3)) + 1 : 1;
                $order->number = sprintf('ENC-%s-%03d', $year, $nextNumber);
            }
        });

        static::updating(function ($order) {
            if ($order->isDirty('status') && $order->status === 'closed' && ! $order->order_date) {
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
            ->logOnly(['number', 'client_id', 'status', 'total_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function clientOrderArticles()
    {
        return $this->hasMany(ClientOrderArticle::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'client_order_articles')
            ->withPivot(['quantity', 'unit_price', 'cost_price', 'subtotal', 'supplier_id'])
            ->withTimestamps();
    }

    public function supplierOrders()
    {
        return $this->hasMany(SupplierOrder::class);
    }
}
