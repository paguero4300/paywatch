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
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->copyable(),

                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->icon('heroicon-o-clock')
                    ->iconColor('gray')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Fecha')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Desde'),
                        DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

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
