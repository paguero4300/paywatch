<?php

namespace App\Filament\Resources\PaymentNotifications\Pages;

use App\Filament\Resources\PaymentNotifications\PaymentNotificationResource;
use Filament\Resources\Pages\ListRecords;

class ListPaymentNotifications extends ListRecords
{
    protected static string $resource = PaymentNotificationResource::class;

    protected static ?string $title = 'Notificaciones de Pago';

    protected static ?string $navigationLabel = 'Notificaciones de Pago';

    protected static string $view = 'filament.resources.payment-notifications.pages.list-payment-notifications';

    public function getHeading(): string
    {
        return 'Notificaciones de Pago';
    }

    public function getSubheading(): ?string
    {
        return 'Actualizándose automáticamente cada 5 segundos';
    }
}
