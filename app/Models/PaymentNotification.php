<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentNotification extends Model
{
    protected $table = 'payment_notifications';

    protected $fillable = [
        'device_id',
        'user_id',
        'app',
        'package_name',
        'title',
        'text',
        'big_text',
        'sub_text',
        'original_message',
        'timestamp',
        'amount',
        'sender',
        'confidence_level',
        'raw_notification_text',
        'migrated',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'timestamp' => 'integer',
        'migrated' => 'boolean',
    ];

    public function device()
    {
        // La FK 'user_id' apunta a 'usuario' (modelo Device)
        return $this->belongsTo(Device::class, 'user_id');
    }

    public function company(): BelongsToMany
    {
        // Relacionar a través de la tabla pivote company_device usando user_id -> device_id
        return $this->belongsToMany(
            Company::class,
            'company_device',
            'device_id',
            'company_id',
            'user_id',
            'id'
        );
    }
}
