<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la Empresa')
                    ->helperText('Nombre completo y oficial de la empresa.')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Distribuidora El Sol SAC')
                    ->prefixIcon('heroicon-o-building-office-2')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, callable $set) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Identificador URL (Slug)')
                    ->helperText('Se genera automáticamente. Solo minúsculas, números y guiones.')
                    ->required()
                    ->unique('companies', 'slug', ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('distribuidora-el-sol-sac')
                    ->prefixIcon('heroicon-o-link')
                    ->suffixIcon('heroicon-o-check-badge')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->validationMessages([
                        'regex' => 'Solo letras minúsculas, números y guiones (-)',
                        'unique' => 'Este slug ya está en uso por otra empresa',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
