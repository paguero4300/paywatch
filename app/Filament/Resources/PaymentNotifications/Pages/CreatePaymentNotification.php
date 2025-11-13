<?php

namespace App\Filament\Resources\PaymentNotifications\Pages;

use App\Filament\Resources\PaymentNotifications\PaymentNotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentNotification extends CreateRecord
{
    protected static string $resource = PaymentNotificationResource::class;
}
