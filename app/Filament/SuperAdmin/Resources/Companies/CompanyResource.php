<?php

namespace App\Filament\SuperAdmin\Resources\Companies;

use App\Filament\SuperAdmin\Resources\Companies\Pages\CreateCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\EditCompany;
use App\Filament\SuperAdmin\Resources\Companies\Pages\ListCompanies;
use App\Filament\SuperAdmin\Resources\Companies\RelationManagers\DevicesRelationManager;
use App\Filament\SuperAdmin\Resources\Companies\RelationManagers\UsersRelationManager;
use App\Filament\SuperAdmin\Resources\Companies\Schemas\CompanyForm;
use App\Filament\SuperAdmin\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Empresas';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestión global';

    protected static ?int $navigationSort = 1;

    public static function scopeToTenant(bool $condition = true): void
    {
        parent::scopeToTenant(false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DevicesRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->is_super_admin ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
