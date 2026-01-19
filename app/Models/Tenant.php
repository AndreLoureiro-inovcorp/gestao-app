<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Owner do tenant
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Todos os utilizadores do tenant
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Subscrição ativa do tenant
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)->latest();
    }

    /**
     * Todas as subscrições do tenant
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Plano atual do tenant
     */
    public function currentPlan()
    {
        return $this->subscription?->plan;
    }

    /**
     * Verificar se user é owner
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Adicionar utilizador ao tenant
     */
    public function addUser(User $user, string $role = 'member'): void
    {
        if (! $this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
            ]);
        }
    }

    /**
     * Remover utilizador do tenant
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    /**
     * Verificar se tenant está dentro do limite do plano
     */
    public function isWithinLimit(string $resource): bool
    {
        $plan = $this->currentPlan();
        
        if (!$plan || !isset($plan->limits[$resource])) {
            return true;
        }

        $limit = $plan->limits[$resource];
        
        if ($limit === 'unlimited') {
            return true;
        }

        $current = match($resource) {
            'users' => $this->users()->count(),
            'proposals' => \App\Models\Proposal::where('tenant_id', $this->id)->count(),
            default => 0,
        };

        return $current < $limit;
    }

    /**
     * Obter uso atual de um recurso
     */
    public function currentUsage(string $resource): int
    {
        return match($resource) {
            'users' => $this->users()->count(),
            'proposals' => \App\Models\Proposal::where('tenant_id', $this->id)->count(),
            default => 0,
        };
    }

    /**
     * Obter limite de um recurso
     */
    public function getLimit(string $resource)
    {
        $plan = $this->currentPlan();
        
        if (!$plan || !isset($plan->limits[$resource])) {
            return 'unlimited';
        }

        return $plan->limits[$resource];
    }

    /**
     * Verificar se está em trial
     */
    public function isOnTrial(): bool
    {
        return $this->subscription?->isOnTrial() ?? false;
    }

    /**
     * Dias restantes de trial
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->isOnTrial()) {
            return null;
        }

        return now()->diffInDays($this->subscription->trial_ends_at);
    }

    /**
     * Configuração do Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
