<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proposal extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'number',
        'proposal_date',
        'client_id',
        'validity_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'proposal_date' => 'date',
        'validity_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate number and validity.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proposal) {
            if (! $proposal->number) {
                $year = date('Y');
                $lastNumber = self::where('number', 'like', "PROP-{$year}-%")->max('number');
                $nextNumber = $lastNumber ? intval(substr($lastNumber, -3)) + 1 : 1;
                $proposal->number = sprintf('PROP-%s-%03d', $year, $nextNumber);
            }

            if (! $proposal->validity_date) {
                $proposal->validity_date = Carbon::now()->addDays(30);
            }
        });

        static::updating(function ($proposal) {
            if ($proposal->isDirty('status') && $proposal->status === 'closed' && ! $proposal->proposal_date) {
                $proposal->proposal_date = Carbon::now();
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

    public function proposalArticles()
    {
        return $this->hasMany(ProposalArticle::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'proposal_articles')
            ->withPivot(['quantity', 'unit_price', 'cost_price', 'subtotal', 'supplier_id'])
            ->withTimestamps();
    }

    /**
     * Get the client order created from this proposal.
     */
    public function clientOrder()
    {
        return $this->hasOne(ClientOrder::class);
    }
}
