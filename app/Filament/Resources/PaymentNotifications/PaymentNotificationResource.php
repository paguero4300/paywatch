<?php

namespace App\Filament\Resources\PaymentNotifications;

use App\Filament\Resources\PaymentNotifications\Pages\CreatePaymentNotification;
use App\Filament\Resources\PaymentNotifications\Pages\EditPaymentNotification;
use App\Filament\Resources\PaymentNotifications\Pages\ListPaymentNotifications;
use App\Filament\Resources\PaymentNotifications\Schemas\PaymentNotificationForm;
use App\Filament\Resources\PaymentNotifications\Tables\PaymentNotificationsTable;
use App\Models\PaymentNotification;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentNotificationResource extends Resource
{
    protected static ?string $model = PaymentNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Notificaciones de Pago';

    protected static ?string $modelLabel = 'Notificación de Pago';

    protected static ?string $pluralModelLabel = 'Notificaciones de Pago';

    protected static string|\UnitEnum|null $navigationGroup = 'Operación';

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    public static function form(Schema $schema): Schema
    {
        return PaymentNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $company = Filament::getTenant();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (!$company) {
            return $user->is_super_admin ? $query : $query->whereRaw('1 = 0');
        }

        $query->whereHas('device.company', fn ($q) => $q->where('companies.id', $company->id));

        if ($user->is_super_admin || $user->isCompanyAdmin($company)) {
            return $query;
        }

        if ($user->isCashier($company)) {
            $accessibleDeviceIds = $user->accessibleDevices()->pluck('usuario.id');
            return $query->whereIn('user_id', $accessibleDeviceIds);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if (!$user) {
            return false;
        }

        return $user->is_super_admin
            || ($tenant && ($user->isCompanyAdmin($tenant) || $user->isCashier($tenant)));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentNotifications::route('/'),
            'create' => CreatePaymentNotification::route('/create'),
            'edit' => EditPaymentNotification::route('/{record}/edit'),
        ];
    }
}
