<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
