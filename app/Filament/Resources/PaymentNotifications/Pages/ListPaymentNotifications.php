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

    public ?int $lastRecordId = null;

    public function mount(): void
    {
        parent::mount();

        // Inicializar con el ID más reciente al cargar la página
        $this->lastRecordId = PaymentNotification::query()
            ->when(
                !auth()->user()->is_super_admin,
                fn ($query) => $query->whereHas('device.company', function ($q) {
                    $q->where('companies.id', \Filament\Facades\Filament::getTenant()?->id);
                })
            )
            ->max('id');
    }

    public function checkForNewRecords(): void
    {
        // Obtener el ID máximo actual
        $currentMaxId = PaymentNotification::query()
            ->when(
                !auth()->user()->is_super_admin,
                fn ($query) => $query->whereHas('device.company', function ($q) {
                    $q->where('companies.id', \Filament\Facades\Filament::getTenant()?->id);
                })
            )
            ->max('id');

        // Si hay nuevos registros
        if ($currentMaxId && $this->lastRecordId && $currentMaxId > $this->lastRecordId) {
            $newCount = $currentMaxId - $this->lastRecordId;

            Notification::make()
                ->title('Nuevos pagos recibidos')
                ->body($newCount === 1 ? '1 nuevo pago registrado' : "{$newCount} nuevos pagos registrados")
                ->success()
                ->icon('heroicon-o-banknotes')
                ->iconColor('success')
                ->duration(3000)
                ->send();

            // Disparar evento para reproducir sonido
            $this->dispatch('play-notification-sound');

            // Actualizar el último ID conocido
            $this->lastRecordId = $currentMaxId;
        } elseif ($currentMaxId && !$this->lastRecordId) {
            // Primera carga
            $this->lastRecordId = $currentMaxId;
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
