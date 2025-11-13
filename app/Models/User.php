<?php

namespace App\Models;

// Imports necesarios para Filament 4 Tenancy
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasTenants, HasDefaultTenant
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Configuración de casts para Laravel 12
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean', // Castear a booleano
        ];
    }

    // --- Relaciones ---

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withPivot('role')->withTimestamps();
    }

    public function accessibleDevices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'cashier_device_access', 'user_id', 'device_id');
    }

    // --- Helpers de Roles (Lógica de Negocio) ---

    public function isCompanyAdmin(?Company $company): bool
    {
        if (!$company) return false;
        return $this->companies()->where('company_id', $company->id)->wherePivot('role', 'admin')->exists();
    }

    public function isCashier(?Company $company): bool
    {
        if (!$company) return false;
        return $this->companies()->where('company_id', $company->id)->wherePivot('role', 'cashier')->exists();
    }

    // --- Implementación de Filament (HasTenants & FilamentUser) ---

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'super-admin' => $this->is_super_admin,
            'admin' => $this->is_super_admin || $this->companies()->exists(),
            default => false,
        };
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        // Los super admins ingresan sin tenant para poder gestionar Companies
        if ($this->is_super_admin) {
            return null;
        }

        return $this->companies()->first();
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($this->is_super_admin) {
            return Company::all();
        }

        return $this->companies;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        return $this->companies->contains($tenant);
    }
}
