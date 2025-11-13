<?php

namespace App\Filament\Resources\PaymentNotifications\Pages;

use App\Filament\Resources\PaymentNotifications\PaymentNotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentNotification extends EditRecord
{
    protected static string $resource = PaymentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
