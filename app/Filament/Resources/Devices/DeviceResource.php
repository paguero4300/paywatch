<?php

namespace App\Filament\Resources\Devices;

use App\Filament\Resources\Devices\Pages\CreateDevice;
use App\Filament\Resources\Devices\Pages\EditDevice;
use App\Filament\Resources\Devices\Pages\ListDevices;
use App\Filament\Resources\Devices\Schemas\DeviceForm;
use App\Filament\Resources\Devices\Tables\DevicesTable;
use App\Models\Device;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'username';

    protected static ?string $navigationLabel = 'Dispositivos';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    public static function form(Schema $schema): Schema
    {
        return DeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->is_super_admin || $user->isCompanyAdmin(filament()->getTenant()) || $user->isCashier(filament()->getTenant());
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user->is_super_admin || $user->isCompanyAdmin(filament()->getTenant());
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if ($user->is_super_admin) {
            return true;
        }
        
        $tenant = filament()->getTenant();
        return $record->company()->where('companies.id', $tenant->id)->exists() && 
               ($user->isCompanyAdmin($tenant) || $user->isCashier($tenant));
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        if ($user->is_super_admin) {
            return true;
        }
        
        $tenant = filament()->getTenant();
        return $record->company()->where('companies.id', $tenant->id)->exists() && 
               $user->isCompanyAdmin($tenant);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $company = filament()->getTenant();

        if (!$company) {
            // Vista Global. Solo Super Admin debería ver esto.
            return $user->is_super_admin ? $query : $query->where('id', 0);
        }

        // Filtrado Adicional por Rol (dentro de la empresa)
        if ($user->isCompanyAdmin($company) || $user->is_super_admin) {
            return $query; // Admins ven todos los dispositivos de la empresa.
        }

        if ($user->isCashier($company)) {
            // Cajeros solo ven los dispositivos asignados explícitamente a ellos.
            return $query->whereHas('cashiers', fn($q) => $q->where('users.id', $user->id));
        }

        return $query->where('id', 0); // Seguridad por defecto.
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevices::route('/'),
            'create' => CreateDevice::route('/create'),
            'edit' => EditDevice::route('/{record}/edit'),
        ];
    }
}
