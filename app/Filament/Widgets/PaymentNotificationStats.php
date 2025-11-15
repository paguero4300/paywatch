<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PaymentNotifications\Pages\ListPaymentNotifications;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentNotificationStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static bool $isDiscovered = false;

    protected function getTablePage(): string
    {
        return ListPaymentNotifications::class;
    }

    protected function getStats(): array
    {
        // Obtener la query con los filtros aplicados
        $query = $this->getPageTableQuery();

        // Calcular estadísticas basadas en los datos filtrados
        $totalPayments = $query->count();
        $totalAmount = $query->sum('amount') ?? 0;
        $averageAmount = $totalPayments > 0 ? $totalAmount / $totalPayments : 0;

        // Obtener el último pago de los datos filtrados
        $lastPayment = $query->latest('created_at')->first();

        return [
            Stat::make('Total de Pagos', number_format($totalPayments, 0, ',', '.'))
                ->description('Cantidad de transacciones')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary')
                ->chart($this->getChartData()),

            Stat::make('Monto Total', 'S/ ' . number_format($totalAmount, 2, '.', ','))
                ->description('Suma de todos los pagos')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Promedio por Pago', 'S/ ' . number_format($averageAmount, 2, '.', ','))
                ->description('Monto promedio')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('warning'),

            Stat::make('Último Pago', $lastPayment ? 'S/ ' . number_format($lastPayment->amount, 2, '.', ',') : 'Sin pagos')
                ->description($lastPayment ? $lastPayment->created_at->diffForHumans() : 'No hay datos')
                ->descriptionIcon('heroicon-o-clock')
                ->color($lastPayment ? 'info' : 'gray'),
        ];
    }

    protected function getChartData(): array
    {
        // Obtener los registros filtrados
        $records = $this->getPageTableRecords();

        // Agrupar por hora del día
        $grouped = $records->groupBy(function ($record) {
            return $record->created_at->format('H');
        })->map->count();

        // Crear array de 24 horas
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[] = $grouped->get(str_pad($i, 2, '0', STR_PAD_LEFT), 0);
        }

        return array_slice($data, 0, 7); // Solo las primeras 7 horas para el chart pequeño
    }
}
