<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $tenantOwnershipRelationshipName = 'companies';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
        return $user->is_super_admin || $user->isCompanyAdmin(filament()->getTenant());
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
        return $user->isCompanyAdmin($tenant) && $record->companies()->where('company_id', $tenant->id)->exists();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
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

        // Nota: Filament automáticamente aplica el scope del Tenant porque la relación
        // 'users' está definida en el modelo Company.

        // Filtrado Adicional por Rol (dentro de la empresa)
        if ($user->isCompanyAdmin($company) || $user->is_super_admin) {
            return $query; // Admins ven todos los usuarios de la empresa.
        }

        if ($user->isCashier($company)) {
            // Cajeros solo ven otros usuarios de la misma empresa (no a sí mismos)
            return $query->where('id', '!=', $user->id);
        }

        return $query->where('id', 0); // Seguridad por defecto.
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
