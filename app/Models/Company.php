<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];

    public function users(): BelongsToMany
    {
        // Incluimos el 'role' del pivote
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'company_device', 'company_id', 'device_id');
    }
}
