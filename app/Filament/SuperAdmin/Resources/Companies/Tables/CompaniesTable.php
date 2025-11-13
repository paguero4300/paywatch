<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre de la Empresa')
                    ->description(fn ($record) => $record->slug)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-building-office-2')
                    ->iconColor('primary'),

                TextColumn::make('slug')
                    ->label('Identificador')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-tag')
                    ->copyable()
                    ->copyMessage('Identificador copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-users')
                    ->sortable(),

                TextColumn::make('devices_count')
                    ->label('Dispositivos')
                    ->counts('devices')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->icon('heroicon-o-clock'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar empresas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar estas empresas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No hay empresas registradas')
            ->emptyStateDescription('Crea tu primera empresa para comenzar a gestionar el sistema.')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }
}
