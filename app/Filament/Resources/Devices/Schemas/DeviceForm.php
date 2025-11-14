<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $company = Filament::getTenant();
        $isAdmin = $user->is_super_admin || ($company && $user->isCompanyAdmin($company));

        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Usuario')
                    ->required()
                    ->unique('usuario', 'username', ignoreRecord: true),
                
                TextInput::make('password_hash')
                    ->label('Clave')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state, ['rounds' => 12]) : null)
                    ->revealable(),
                
                TextInput::make('device_id')
                    ->label('ID del dispositivo (App)')
                    ->placeholder('El dispositivo lo completará automáticamente')
                    ->dehydrateStateUsing(fn ($state) => $state ?? '')
                    ->unique('usuario', 'device_id', ignoreRecord: true),

                // Asignación de Cajeros (Solo visible y editable por Admins)
                Select::make('cashiers')
                    ->label('Cajeros con Acceso')
                    ->multiple()
                    ->relationship('cashiers', 'name')
                    ->preload()
                    // Filtrar opciones: mostrar solo usuarios con rol 'cashier' de la empresa actual
                    ->options(function () use ($company) {
                        if (!$company) return [];
                        return $company->users()->wherePivot('role', 'cashier')->pluck('users.name', 'users.id');
                    })
                    ->visible($isAdmin)
                    ->disabled(!$isAdmin),
            ]);
    }
}
