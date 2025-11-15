<?php

namespace App\Filament\Resources\PaymentNotifications\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.username')
                    ->label('Dispositivo')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->iconColor('info')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->tooltip('Click para copiar'),

                TextColumn::make('app')
                    ->label('Aplicación')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'yape' => 'warning',
                        'plin' => 'purple',
                        'tunki' => 'success',
                        'bcp' => 'info',
                        'interbank' => 'primary',
                        'bbva' => 'indigo',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match (strtolower($state)) {
                        'yape' => 'heroicon-o-currency-dollar',
                        'plin' => 'heroicon-o-banknotes',
                        'tunki' => 'heroicon-o-credit-card',
                        default => 'heroicon-o-wallet',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('success')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->copyable()
                    ->tooltip('Click para copiar')
                    ->summarize(Sum::make()->label('Total')->money('PEN')),

                TextColumn::make('sender')
                    ->label('Remitente')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '-';
                        }
                        // Limpiar "Confirmación de Pago" y asteriscos
                        $cleaned = str_replace(['***', 'Confirmación de Pago'], '', $state);
                        $cleaned = trim($cleaned);

                        return $cleaned ?: '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary')
                    ->limit(35)
                    ->tooltip(fn ($state) => $state ? str_replace(['***', 'Confirmación de Pago'], '', trim($state)) : '-')
                    ->copyable()
                    ->copyMessage('Remitente copiado'),

                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->icon('heroicon-o-clock')
                    ->iconColor('gray')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Filter::make('hoy')
                    ->label('Solo Hoy')
                    ->default()
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        $startOfDay = now()->startOfDay()->timestamp;
                        $endOfDay = now()->endOfDay()->timestamp;
                        return $query->whereBetween('timestamp', [$startOfDay, $endOfDay]);
                    })
                    ->indicateUsing(fn (): string => 'Mostrando pagos de hoy (' . now()->format('d/m/Y') . ')'),

                Filter::make('fecha_pago')
                    ->label('Rango de Fechas')
                    ->schema([
                        DatePicker::make('fecha_desde')
                            ->label('Desde')
                            ->placeholder('Selecciona fecha inicial')
                            ->native(false),
                        DatePicker::make('fecha_hasta')
                            ->label('Hasta')
                            ->placeholder('Selecciona fecha final')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date): Builder => $query->where('timestamp', '>=', \Carbon\Carbon::parse($date)->startOfDay()->timestamp),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date): Builder => $query->where('timestamp', '<=', \Carbon\Carbon::parse($date)->endOfDay()->timestamp),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['fecha_desde'] && !$data['fecha_hasta']) {
                            return null;
                        }

                        $from = $data['fecha_desde'] ? \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') : '...';
                        $until = $data['fecha_hasta'] ? \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') : '...';

                        return "Rango: {$from} - {$until}";
                    }),

                Filter::make('esta_semana')
                    ->label('Esta Semana')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        $startOfWeek = now()->startOfWeek()->timestamp;
                        $endOfWeek = now()->endOfWeek()->timestamp;
                        return $query->whereBetween('timestamp', [$startOfWeek, $endOfWeek]);
                    })
                    ->indicateUsing(fn (): string => 'Mostrando pagos de esta semana'),

                Filter::make('este_mes')
                    ->label('Este Mes')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        $startOfMonth = now()->startOfMonth()->timestamp;
                        $endOfMonth = now()->endOfMonth()->timestamp;
                        return $query->whereBetween('timestamp', [$startOfMonth, $endOfMonth]);
                    })
                    ->indicateUsing(fn (): string => 'Mostrando pagos de este mes'),

                SelectFilter::make('app')
                    ->label('Aplicación')
                    ->options([
                        'Yape' => 'Yape',
                        'Plin' => 'Plin',
                        'Tunki' => 'Tunki',
                        'BCP' => 'BCP',
                        'Interbank' => 'Interbank',
                        'BBVA' => 'BBVA',
                    ])
                    ->multiple(),

                SelectFilter::make('device')
                    ->label('Dispositivo')
                    ->relationship('device', 'username')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->persistFiltersInSession()
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
