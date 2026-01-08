<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierInvoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'invoice_date',
        'due_date',
        'supplier_id',
        'supplier_order_id',
        'total_amount',
        'document_path',
        'payment_proof_path',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot method to set paid_at when status changes.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($invoice) {
            if ($invoice->isDirty('status') && $invoice->status === 'paid' && ! $invoice->paid_at) {
                $invoice->paid_at = Carbon::now();
            }
        });
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'supplier_id', 'status', 'total_amount', 'paid_at'])
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

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && Carbon::now()->gt($this->due_date);
    }

    /**
     * Get days until due (negative if overdue)
     */
    public function daysUntilDue()
    {
        return Carbon::now()->diffInDays($this->due_date, false);
    }
}
