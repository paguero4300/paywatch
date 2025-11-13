<?php

namespace App\Filament\SuperAdmin\Resources\Companies\RelationManagers;

use App\Models\Device;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    protected static ?string $title = 'Dispositivos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('username')
                    ->label('Nombre de Usuario')
                    ->helperText('Identificador único para autenticación del dispositivo.')
                    ->required()
                    ->unique('usuario', 'username', ignoreRecord: true)
                    ->maxLength(150)
                    ->placeholder('dispositivo-caja-01')
                    ->prefixIcon('heroicon-o-identification')
                    ->suffixIcon('heroicon-o-check-circle')
                    ->columnSpan(1),

                TextInput::make('password_hash')
                    ->label('Contraseña')
                    ->helperText(fn (string $context) => $context === 'create'
                        ? 'Mínimo 8 caracteres. Será encriptada automáticamente.'
                        : 'Dejar en blanco para mantener la contraseña actual.')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : $state)
                    ->dehydrated(fn ($state) => filled($state))
                    ->minLength(8)
                    ->maxLength(255)
                    ->placeholder('••••••••')
                    ->prefixIcon('heroicon-o-lock-closed')
                    ->revealable()
                    ->columnSpan(1),

                TextInput::make('device_id')
                    ->label('ID del Dispositivo')
                    ->helperText('Este campo se completa automáticamente cuando el dispositivo se conecta por primera vez al sistema.')
                    ->placeholder('⏳ Pendiente - Se asignará automáticamente')
                    ->dehydrateStateUsing(fn ($state) => $state ?? '')
                    ->unique('usuario', 'device_id', ignoreRecord: true)
                    ->maxLength(255)
                    ->prefixIcon('heroicon-o-device-phone-mobile')
                    ->suffixIcon(fn (string $context) => $context === 'create' ? 'heroicon-o-clock' : 'heroicon-o-information-circle')
                    ->disabled(fn (string $context): bool => $context === 'create')
                    ->columnSpanFull()
                    ->dehydrated(fn (string $context): bool => $context !== 'create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('Usuario del Dispositivo')
                    ->description(fn ($record) => $record->device_id ?: 'ID pendiente')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->iconColor('primary'),

                TextColumn::make('device_id')
                    ->label('ID del Dispositivo')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Pendiente de registro')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->copyable()
                    ->copyMessage('ID copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->icon('heroicon-o-calendar'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar Dispositivo')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Registrar nuevo dispositivo')
                    ->modalDescription('Configure las credenciales para un nuevo dispositivo de esta empresa.')
                    ->modalSubmitActionLabel('Registrar dispositivo')
                    ->successNotificationTitle('Dispositivo registrado exitosamente')
                    ->using(function (array $data, RelationManager $livewire): Device {
                        $device = Device::create($data);
                        $livewire->getOwnerRecord()->devices()->attach($device);

                        return $device;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Editar dispositivo')
                    ->modalDescription('Modifique las credenciales o el ID del dispositivo.')
                    ->modalSubmitActionLabel('Guardar cambios')
                    ->successNotificationTitle('Dispositivo actualizado exitosamente')
                    ->mutateRecordDataUsing(fn (array $data): array => Arr::except($data, 'password_hash'))
                    ->using(function (array $data, RelationManager $_, Device $record, ?Table $table = null): Device {
                        if (blank($data['password_hash'] ?? null)) {
                            unset($data['password_hash']);
                        }

                        $record->update($data);

                        return $record;
                    }),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('Eliminar dispositivo')
                    ->modalDescription('¿Está seguro de que desea eliminar este dispositivo? Esta acción eliminará el dispositivo y todas sus notificaciones asociadas.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->successNotificationTitle('Dispositivo eliminado exitosamente')
                    ->before(function (DeleteAction $action): void {
                        $company = $action->getLivewire()->getOwnerRecord();
                        $company->devices()->detach($action->getRecord()->id);
                    })
                    ->using(fn (Device $record): bool => (bool) $record->delete()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->icon('heroicon-o-trash')
                        ->modalHeading('Eliminar dispositivos')
                        ->modalDescription('¿Está seguro de que desea eliminar estos dispositivos? Esta acción eliminará los dispositivos y todas sus notificaciones asociadas.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->successNotificationTitle('Dispositivos eliminados exitosamente')
                        ->before(function (DeleteBulkAction $action): void {
                            $company = $action->getLivewire()->getOwnerRecord();
                            $ids = $action->getRecords()->pluck('id');
                            $company->devices()->detach($ids);
                        })
                        ->using(function (DeleteBulkAction $action, $records): void {
                            $records->each->delete();
                        }),
                ]),
            ])
            ->emptyStateHeading('No hay dispositivos registrados')
            ->emptyStateDescription('Registre dispositivos para comenzar a recibir notificaciones de pago.')
            ->emptyStateIcon('heroicon-o-device-phone-mobile');
    }
}
