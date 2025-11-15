<?php

namespace App\Filament\Resources\PaymentNotifications\Pages;

use App\Filament\Resources\PaymentNotifications\PaymentNotificationResource;
use App\Models\PaymentNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListPaymentNotifications extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = PaymentNotificationResource::class;

    protected static ?string $title = 'Notificaciones de Pago';

    protected static ?string $navigationLabel = 'Notificaciones de Pago';

    protected string $view = 'filament.resources.payment-notifications.pages.list-payment-notifications';

    public ?int $lastPaymentTimestamp = null;

    public function mount(): void
    {
        parent::mount();

        // Inicializar con el timestamp más reciente al cargar la página
        $this->lastPaymentTimestamp = PaymentNotification::query()
            ->when(
                !auth()->user()->is_super_admin,
                fn ($query) => $query->whereHas('device.company', function ($q) {
                    $q->where('companies.id', \Filament\Facades\Filament::getTenant()?->id);
                })
            )
            ->max('timestamp');
    }

    public function checkForNewRecords(): void
    {
        // Obtener el timestamp máximo actual (fecha real del pago)
        $currentMaxTimestamp = PaymentNotification::query()
            ->when(
                !auth()->user()->is_super_admin,
                fn ($query) => $query->whereHas('device.company', function ($q) {
                    $q->where('companies.id', \Filament\Facades\Filament::getTenant()?->id);
                })
            )
            ->max('timestamp');

        // Si hay nuevos registros (timestamp más reciente)
        if ($currentMaxTimestamp && $this->lastPaymentTimestamp && $currentMaxTimestamp > $this->lastPaymentTimestamp) {
            // Contar cuántos pagos nuevos hay
            $newCount = PaymentNotification::query()
                ->when(
                    !auth()->user()->is_super_admin,
                    fn ($query) => $query->whereHas('device.company', function ($q) {
                        $q->where('companies.id', \Filament\Facades\Filament::getTenant()?->id);
                    })
                )
                ->where('timestamp', '>', $this->lastPaymentTimestamp)
                ->count();

            Notification::make()
                ->title('Nuevos pagos recibidos')
                ->body($newCount === 1 ? '1 nuevo pago registrado' : "{$newCount} nuevos pagos registrados")
                ->success()
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->duration(3000)
                ->send();

            // Actualizar el último timestamp conocido
            $this->lastPaymentTimestamp = $currentMaxTimestamp;
        } elseif ($currentMaxTimestamp && !$this->lastPaymentTimestamp) {
            // Primera carga
            $this->lastPaymentTimestamp = $currentMaxTimestamp;
        }
    }

    public function getHeading(): string
    {
        return 'Notificaciones de Pago';
    }

    public function getSubheading(): ?string
    {
        return 'Actualizándose automáticamente cada 5 segundos';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PaymentNotificationStats::class,
        ];
    }
}
