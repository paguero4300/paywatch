<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                // Mostrar el rol específico para el tenant actual
                TextColumn::make('role_display')
                   ->label('Rol')
                   ->getStateUsing(function (User $record) {
                       $company = Filament::getTenant();
                       // Buscar el rol en la tabla pivote para la empresa actual
                       $pivot = $record->companies()->where('company_id', $company?->id)->first()?->pivot;
                       return $pivot?->role;
                   })->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
