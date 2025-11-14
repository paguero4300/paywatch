<?php

namespace App\Filament\Widgets;

use App\Models\PaymentNotification;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PaymentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $company = Filament::getTenant();

        // Base query con scoping de permisos
        $query = PaymentNotification::query()
            ->when(
                $company && !$user->is_super_admin,
                fn ($q) => $q->whereHas('device.company', fn ($subQ) => $subQ->where('companies.id', $company->id))
            )
            ->when(
                $company && $user->isCashier($company),
                fn ($q) => $q->whereIn('user_id', $user->accessibleDevices()->pluck('usuario.id'))
            );

        // Estadísticas de hoy
        $today = now()->startOfDay();
        $paymentsToday = (clone $query)->where('created_at', '>=', $today)->count();
        $amountToday = (clone $query)->where('created_at', '>=', $today)->sum('amount') ?? 0;

        // Total general
        $totalPayments = (clone $query)->count();
        $totalAmount = (clone $query)->sum('amount') ?? 0;

        // Último pago
        $lastPayment = (clone $query)->latest('created_at')->first();

        return [
            Stat::make('Pagos Hoy', $paymentsToday)
                ->description('Total de transacciones registradas hoy')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary')
                ->chart($this->getLastSevenDaysChart($query)),

            Stat::make('Monto Hoy', 'S/ ' . number_format($amountToday, 2))
                ->description('Ingresos del día actual')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Total Pagos', $totalPayments)
                ->description('Todas las transacciones registradas')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Último Pago', $lastPayment ? 'S/ ' . number_format($lastPayment->amount, 2) : 'Sin pagos')
                ->description($lastPayment ? $lastPayment->created_at->diffForHumans() : 'No hay registros')
                ->descriptionIcon('heroicon-o-clock')
                ->color($lastPayment ? 'warning' : 'gray'),
        ];
    }

    protected function getLastSevenDaysChart($query): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = (clone $query)
                ->whereBetween('created_at', [$date, $date->copy()->endOfDay()])
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
