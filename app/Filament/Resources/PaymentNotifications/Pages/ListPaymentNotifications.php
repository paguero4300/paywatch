<?php

namespace App\Filament\Resources\PaymentNotifications\Pages;

use App\Filament\Resources\PaymentNotifications\PaymentNotificationResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentNotifications extends ListRecords
{
    protected static string $resource = PaymentNotificationResource::class;
}
