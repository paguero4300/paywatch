<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Device extends Model
{
    // Indicar que usamos la tabla existente 'usuario'
    protected $table = 'usuario';

    protected $fillable = ['username', 'password_hash', 'device_id'];
    protected $hidden = ['password_hash'];

    // Relación con Company a través de company_device
    public function company(): BelongsToMany
    {
        // Aunque lógicamente es 1-a-1 por la restricción unique, Eloquent lo maneja así con tablas pivote.
        return $this->belongsToMany(Company::class, 'company_device', 'device_id', 'company_id');
    }

    // Relación con los Users (Cajeros) que tienen acceso
    public function cashiers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cashier_device_access', 'device_id', 'user_id');
    }

    public function paymentNotifications()
    {
        // La FK en payment_notifications se llama 'user_id'.
        return $this->hasMany(PaymentNotification::class, 'user_id');
    }
}
