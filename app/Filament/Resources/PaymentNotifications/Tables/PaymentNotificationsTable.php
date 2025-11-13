<?php

namespace App\Filament\Resources\PaymentNotifications\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.username')
                    ->label('Dispositivo')
                    ->searchable(),
                TextColumn::make('app')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->numeric(2),
                TextColumn::make('sender')
                    ->label('Remitente')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
