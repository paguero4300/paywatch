<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuarios de la empresa';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre Completo')
                    ->helperText('Nombre y apellidos del usuario.')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Juan Pérez García')
                    ->prefixIcon('heroicon-o-user')
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->helperText('Usado para iniciar sesión.')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('usuario@ejemplo.com')
                    ->prefixIcon('heroicon-o-envelope')
                    ->suffixIcon('heroicon-o-at-symbol')
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->helperText(fn (string $context) => $context === 'create'
                        ? 'Mínimo 8 caracteres. Será encriptada automáticamente.'
                        : 'Dejar en blanco para mantener la contraseña actual.')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : $state)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->maxLength(255)
                    ->placeholder('••••••••')
                    ->prefixIcon('heroicon-o-key')
                    ->revealable()
                    ->columnSpan(1),

                Select::make('role')
                    ->label('Rol en la Empresa')
                    ->helperText('Define los permisos del usuario.')
                    ->options([
                        'admin' => 'Administrador',
                        'cashier' => 'Cajero',
                    ])
                    ->default('cashier')
                    ->required()
                    ->native(false)
                    ->prefixIcon('heroicon-o-shield-check')
                    ->searchable()
                    ->columnSpan(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('pivot.role')
                    ->label('Rol')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear usuario')
                    ->mutateFormDataUsing(function (array $data): array {
                        $role = $data['role'] ?? 'cashier';
                        $data['pivotData'] = ['role' => $role];
                        unset($data['role']);

                        return $data;
                    })
                    ->using(function (RelationManager $livewire, CreateAction $action, array $data): User {
                        $pivot = Arr::pull($data, 'pivotData', []);
                        $user = $action->getModel()::create($data);
                        $livewire->getOwnerRecord()->users()->attach($user, $pivot);

                        return $user;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->mutateRecordDataUsing(function (array $data, EditAction $action): array {
                        $relationship = $action->getTable()->getRelationship();
                        $pivot = $action->getRecord()->getRelationValue($relationship->getPivotAccessor());
                        $data['role'] = $pivot?->role;

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pivotData'] = ['role' => $data['role'] ?? 'cashier'];
                        unset($data['role']);

                        return $data;
                    })
                    ->using(function (RelationManager $livewire, EditAction $action, array $data): User {
                        $pivot = Arr::pull($data, 'pivotData', []);
                        $action->getRecord()->update($data);

                        if (! empty($pivot)) {
                            $livewire->getOwnerRecord()->users()->updateExistingPivot($action->getRecord()->id, $pivot);
                        }

                        return $action->getRecord();
                    }),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->before(function (DeleteAction $action): void {
                        $action->getRecord()->companies()->detach($action->getLivewire()->getOwnerRecord()->id);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->before(function (DeleteBulkAction $action): void {
                            $company = $action->getLivewire()->getOwnerRecord();
                            $company->users()->detach($action->getRecords()->pluck('id'));
                        }),
                ]),
            ]);
    }
}
