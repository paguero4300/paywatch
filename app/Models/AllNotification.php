<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllNotification extends Model
{
    protected $table = 'all_notifications';

    protected $fillable = [
        'device_id',
        'user_id',
        'package_name',
        'app_name',
        'title',
        'text',
        'big_text',
        'sub_text',
        'timestamp',
        'is_payment_app',
        'category',
        'synced',
    ];

    protected $casts = [
        'is_payment_app' => 'boolean',
        'synced' => 'boolean',
        'timestamp' => 'integer',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'user_id');
    }
}
