<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Entity extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'number',
        'type',
        'tax_number',
        'name',
        'address',
        'postal_code',
        'city',
        'country_id',
        'phone',
        'mobile',
        'website',
        'email',
        'gdpr_consent',
        'notes',
        'status',
    ];

    protected $casts = [
        'type' => 'array',
        'gdpr_consent' => 'boolean',
    ];

    /**
     * Boot method to auto-generate number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entity) {
            if (! $entity->number) {
                $entity->number = self::max('number') + 1;
            }
        });
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'tax_number', 'type', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all contacts for this entity.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get all proposals where this entity is the client.
     */
    public function proposalsAsClient()
    {
        return $this->hasMany(Proposal::class, 'client_id');
    }

    /**
     * Get all proposal articles where this entity is the supplier.
     */
    public function proposalArticlesAsSupplier()
    {
        return $this->hasMany(ProposalArticle::class, 'supplier_id');
    }

    /**
     * Get all client orders where this entity is the client.
     */
    public function clientOrdersAsClient()
    {
        return $this->hasMany(ClientOrder::class, 'client_id');
    }

    /**
     * Get all client order articles where this entity is the supplier.
     */
    public function clientOrderArticlesAsSupplier()
    {
        return $this->hasMany(ClientOrderArticle::class, 'supplier_id');
    }

    /**
     * Get all supplier orders where this entity is the supplier.
     */
    public function supplierOrdersAsSupplier()
    {
        return $this->hasMany(SupplierOrder::class, 'supplier_id');
    }

    /**
     * Get all supplier invoices where this entity is the supplier.
     */
    public function supplierInvoicesAsSupplier()
    {
        return $this->hasMany(SupplierInvoice::class, 'supplier_id');
    }

    /**
     * Scopes for filtering by type
     */
    public function scopeClients($query)
    {
        return $query->whereJsonContains('type', 'client');
    }

    public function scopeSuppliers($query)
    {
        return $query->whereJsonContains('type', 'supplier');
    }
}
