<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'state',
        'city',
        'role_id',
        'is_approved',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'phone' => 'encrypted',
        'address' => 'encrypted',
        'state' => 'encrypted',
        'city' => 'encrypted',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hasRole(string $roleName): bool
    {
        // Primary role_id is the single source of truth when present.
        if ($this->role_id) {
            if ($this->relationLoaded('role')) {
                return $this->role?->name === $roleName;
            }

            return $this->role()->where('name', $roleName)->exists();
        }

        // Backward-compatible fallback for legacy records.
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->role_id && $this->role()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    public function assignRole(Role $role): void
    {
        $this->role_id = $role->id;
        $this->save();

        // Ensure old roles are removed to avoid accidental privilege carryover.
        $this->roles()->sync([$role->id]);
    }

    /**
     * Safe display value for encrypted phone (avoids 500 when decryption fails, e.g. wrong APP_KEY or legacy plain text).
     */
    public function getDisplayPhone(): string
    {
        try {
            $v = $this->getAttributes()['phone'] ?? null;
            if ($v === null || $v === '') {
                return '';
            }
            return decrypt($v);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Safe display value for encrypted address.
     */
    public function getDisplayAddress(): string
    {
        try {
            $v = $this->getAttributes()['address'] ?? null;
            if ($v === null || $v === '') {
                return '';
            }
            return decrypt($v);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Safe display value for encrypted city.
     */
    public function getDisplayCity(): string
    {
        try {
            $v = $this->getAttributes()['city'] ?? null;
            if ($v === null || $v === '') {
                return '';
            }
            return decrypt($v);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Safe display value for encrypted state.
     */
    public function getDisplayState(): string
    {
        try {
            $v = $this->getAttributes()['state'] ?? null;
            if ($v === null || $v === '') {
                return '';
            }
            return decrypt($v);
        } catch (\Throwable) {
            return '';
        }
    }
}
