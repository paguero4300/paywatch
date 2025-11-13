# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PayWatch is a multi-tenant Laravel 12 + Filament 4 application that manages payment notifications from Flutter mobile devices. The system tracks notifications from payment apps (like Yape, Plin) and provides a hierarchical admin interface for managing companies, devices, and users with role-based access control.

### Core Architecture

**Multi-Tenant Structure:**
- **Super Admins**: Global access to all companies and system management
- **Company Admins**: Manage users and devices within their assigned company
- **Cashiers**: View only their assigned devices and related payment notifications
- **Devices (Flutter Apps)**: Represented by the `usuario` table, send payment notifications to the backend

**Key Design Decisions:**
- `usuario` table stores device credentials (not human users)
- `users` table stores admin/cashier accounts (human users)
- `companies` table acts as tenants with slug-based routing
- Pivot tables manage many-to-many relationships with role data

## Technology Stack

- **Framework**: Laravel 12 with PHP 8.2+
- **Admin Panel**: Filament 4 with multi-tenancy enabled
- **Database**: SQLite (development), MySQL (production)
- **Frontend**: Vite + TailwindCSS 4
- **Testing**: Pest PHP 4
- **Code Style**: Laravel Pint

## CRITICAL: Filament 4 Specific Constraints

**NEVER use these components - they DO NOT exist in Filament 4:**
- ❌ `Filament\Forms\Components\Section` - Does NOT exist
- ❌ `Filament\Forms\Components\Grid` - Does NOT exist
- ❌ `Filament\Forms\Components\Fieldset` - Does NOT exist
- ❌ `->databaseNotifications()` - Requires notifications table (not present)
- ❌ Wrong navigationGroups syntax (array format doesn't work)

**Only use these REAL Filament 4 form components:**
- ✅ `Filament\Forms\Components\TextInput`
- ✅ `Filament\Forms\Components\Select`
- ✅ `Filament\Forms\Components\Textarea`
- ✅ `Filament\Forms\Components\Checkbox`
- ✅ `Filament\Forms\Components\DatePicker`
- ✅ `Filament\Forms\Components\Toggle`

**Correct action imports for RelationManagers in Filament 4:**
- ✅ `use Filament\Actions\CreateAction;`
- ✅ `use Filament\Actions\EditAction;`
- ✅ `use Filament\Actions\DeleteAction;`
- ❌ NOT `Filament\Tables\Actions\*` (this was Filament 3)

## Common Development Commands

```bash
# Development environment (runs server + queue + vite concurrently)
composer run dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
npm run dev

# Setup project from scratch
composer run setup

# Testing
composer test                              # Run all tests
php artisan test                           # Alternative test command
php artisan test --filter=TestName         # Run specific test
vendor/bin/pest --filter=TestName          # Run specific Pest test

# Code quality
vendor/bin/pint --dirty                    # Format modified files
vendor/bin/pint                            # Format all files

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Build frontend assets
npm run build
```

## Custom Artisan Commands

Located in `app/Console/Commands/`:

```bash
# Create initial super admin user
php artisan app:create-super-admin

# Create test company for development
php artisan app:create-test-company

# Create demo devices for testing
php artisan app:create-demo-devices

# Associate existing admin with company
php artisan app:associate-admin-with-company

# Check paywatch table structure
php artisan app:check-paywatch-tables
```

## Database Architecture

### Core Tables

**users** - Admin and cashier accounts
- `is_super_admin` boolean flag for system-wide access
- Uses Laravel's default authentication structure
- Implements Filament's `FilamentUser`, `HasTenants`, and `HasDefaultTenant` contracts

**companies** - Tenant organizations
- `slug` field used for tenant identification in URLs (e.g., `/admin/company-slug`)
- Each company can have multiple users and devices

**usuario** - Device credentials (mapped to `Device` model)
- Stores Flutter app authentication
- `username`, `password_hash`, `device_id` fields
- One device belongs to exactly one company (via `company_device` pivot)

**payment_notifications** - Parsed payment data
- Links to devices via `user_id` foreign key (references `usuario.id`)
- Contains `amount`, `app`, `sender`, `confidence_level` fields
- Indexed on `device_id`, `app`, `timestamp`, `confidence_level`

**all_notifications** - Raw notification data
- All notifications from devices before filtering
- `is_payment_app` boolean to identify payment-related notifications
- Similar structure to `payment_notifications` but includes all app types

### Pivot Tables

**company_user** - User-Company association with roles
- `role` field: 'admin' or 'cashier'
- Determines permissions within specific company context
- Unique constraint on `[company_id, user_id]`

**company_device** - Device-Company association
- **Unique constraint on `device_id`**: Each device belongs to exactly ONE company
- Links `companies.id` to `usuario.id`

**cashier_device_access** - Cashier-Device permissions
- Restricts which devices a cashier can view
- Unique constraint on `[user_id, device_id]`

## Filament Multi-Tenancy Implementation

### Panel Structure

**Super Admin Panel** (`/super-admin`)
- Path: `app/Filament/SuperAdmin/`
- No tenant scoping
- Only accessible to users with `is_super_admin = true`
- Manages system-wide resources

**Admin Panel** (`/admin/{company-slug}`)
- Path: `app/Filament/Resources/`
- Tenant: `Company` model with `slug` attribute
- Accessible to super admins and company-associated users
- Resources auto-scoped to current tenant

### Access Control Pattern

Resources use `getEloquentQuery()` to scope data:

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();
    $company = Filament::getTenant();

    // Super admin sees everything
    if ($user->is_super_admin) {
        return $query;
    }

    // Company admin sees all company data
    if ($user->isCompanyAdmin($company)) {
        return $query;
    }

    // Cashier sees only assigned devices
    if ($user->isCashier($company)) {
        return $query->whereHas('cashiers', fn($q) => $q->where('users.id', $user->id));
    }

    return $query->where('id', 0); // Default deny
}
```

## Model Relationships

### User Model (`app/Models/User.php`)
- `companies()`: BelongsToMany with `role` pivot data
- `accessibleDevices()`: BelongsToMany via `cashier_device_access`
- Helper methods: `isCompanyAdmin()`, `isCashier()`
- Implements Filament tenancy: `getTenants()`, `canAccessTenant()`, `getDefaultTenant()`

### Company Model (`app/Models/Company.php`)
- `users()`: BelongsToMany with `role` pivot data
- `devices()`: BelongsToMany via `company_device`

### Device Model (`app/Models/Device.php`)
- Maps to `usuario` table
- `company()`: BelongsToMany via `company_device` (logically 1-to-1)
- `cashiers()`: BelongsToMany via `cashier_device_access`
- `paymentNotifications()`: HasMany to `PaymentNotification` via `user_id`

### PaymentNotification Model (`app/Models/PaymentNotification.php`)
- `device()`: BelongsTo `Device` via `user_id` foreign key

## Filament Resource Patterns

### Resource Organization

Resources follow a structured schema pattern:
```
app/Filament/Resources/
├── Companies/
│   ├── CompanyResource.php
│   ├── Pages/
│   │   ├── CreateCompany.php
│   │   ├── EditCompany.php
│   │   └── ListCompanies.php
│   ├── RelationManagers/
│   │   └── UsersRelationManager.php
│   ├── Schemas/
│   │   └── CompanyForm.php
│   └── Tables/
│       └── CompaniesTable.php
├── Devices/
├── PaymentNotifications/
└── Users/
```

### Handling Pivot Data

When creating/editing records with pivot relationships:

**Create Pattern:**
```php
protected function handleRecordCreation(array $data): Model
{
    $role = $data['role'];
    unset($data['role']);

    $record = static::getModel()::create($data);

    $company = Filament::getTenant();
    $company->users()->attach($record, ['role' => $role]);

    return $record;
}
```

**Edit Pattern:**
```php
protected function mutateFormDataBeforeFill(array $data): array
{
    $company = Filament::getTenant();
    $data['role'] = $this->getRecord()
        ->companies()
        ->where('company_id', $company?->id)
        ->first()?->pivot->role;
    return $data;
}

protected function handleRecordUpdate(Model $record, array $data): Model
{
    $role = $data['role'];
    unset($data['role']);

    $record->update($data);

    $company = Filament::getTenant();
    $company->users()->updateExistingPivot($record->id, ['role' => $role]);

    return $record;
}
```

### RelationManagers with Pivot Data

**CRITICAL**: When working with RelationManagers that manage many-to-many relationships with pivot data, you must properly handle the pivot fields. This is essential for the User-Company relationship where the `role` field is stored in the `company_user` pivot table.

#### Important Imports (Filament 4)
In Filament 4, table actions in RelationManagers must be imported from `Filament\Actions\*`:
```php
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
```

**CRITICAL NOTES for Filament 4:**
- ❌ Actions are NOT in `Filament\Tables\Actions\*` namespace (this was Filament 3)
- ❌ DO NOT use `Section` or `Grid` in forms - they don't exist in Filament 4
- ✅ Use only basic form components: TextInput, Select, Textarea, etc.
- ✅ Layout is handled by Filament automatically, no manual grid needed

#### Create Action with Pivot Data
```php
CreateAction::make()
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
    })
```

#### Edit Action with Pivot Data
```php
EditAction::make()
    ->mutateRecordDataUsing(function (array $data, EditAction $action): array {
        // Load pivot data before editing
        $relationship = $action->getTable()->getRelationship();
        $pivot = $action->getRecord()->getRelationValue($relationship->getPivotAccessor());
        $data['role'] = $pivot?->role;
        return $data;
    })
    ->mutateFormDataUsing(function (array $data): array {
        // Extract role before saving
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
    })
```

#### Delete Action (Detach from Pivot)
```php
DeleteAction::make()
    ->before(function (DeleteAction $action): void {
        // Only detach from pivot, don't delete the user record
        $action->getRecord()->companies()->detach($action->getLivewire()->getOwnerRecord()->id);
    })
```

#### Displaying Pivot Data in Table
```php
TextColumn::make('pivot.role')
    ->label('Rol')
    ->badge()
```

**Key Pattern**: The pivot field (`role`) must be:
1. Extracted from form data in `mutateFormDataUsing()`
2. Stored in `pivotData` array
3. Removed from main data with `Arr::pull()`
4. Attached/updated separately using `attach()` or `updateExistingPivot()`

## Role-Based Visibility

### Form Field Visibility
```php
Select::make('cashiers')
    ->visible($isAdmin)
    ->disabled(!$isAdmin)
```

### Resource Navigation
```php
public static function shouldRegisterNavigation(): bool
{
    return Filament::getTenant() === null && auth()->user()?->is_super_admin;
}
```

### Resource Access
```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    $company = Filament::getTenant();
    return $user->is_super_admin || $user->isCompanyAdmin($company);
}
```

## Security Considerations

- **Device passwords**: Stored as bcrypt hashes in `usuario.password_hash`
- **Foreign key cascades**: `onDelete('cascade')` for company-related data
- **Soft constraints**: Payment notifications use `SET NULL` to preserve data if device deleted
- **Unique device ownership**: Enforced by unique constraint on `company_device.device_id`
- **CSRF protection**: Enabled via Filament middleware stack
- **Authentication**: Standard Laravel authentication for admin users

## Development Guidelines

### Code Style
- PSR-12 standards (enforced by Laravel Pint)
- Strict typing: Use type hints and `declare(strict_types=1)`
- Eloquent over raw SQL queries
- Form Request validation for complex validation logic

### Testing
- Pest PHP for feature and unit tests
- Factory pattern for test data generation
- Arrange-Act-Assert pattern

### Naming Conventions
- Models: Singular PascalCase (e.g., `PaymentNotification`)
- Tables: Plural snake_case (e.g., `payment_notifications`)
- Controllers: Singular PascalCase + `Controller` (e.g., `DeviceController`)
- Filament Resources: Singular PascalCase + `Resource` (e.g., `DeviceResource`)

## Environment Configuration

Default configuration uses SQLite for development:
```env
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

For production, switch to MySQL and configure:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paywatch
DB_USERNAME=root
DB_PASSWORD=
```

## Project-Specific Documentation

Additional documentation in `documentacion/`:
- `estructura.md`: Detailed multi-tenant architecture guide (in Spanish)
- `tablas.sql`: Original table structure from existing database

## Important Notes

- The `usuario` table represents **devices**, not human users
- The `user_id` foreign key in `payment_notifications` references `usuario.id` (device ID)
- Super admins bypass all tenant restrictions
- Company admins can only manage their assigned company
- Cashiers have read-only access to assigned devices only
- Device-to-company assignment is immutable (unique constraint)
- All Filament resources auto-discover from their respective directories
- The admin panel uses tenant middleware that automatically filters data by company
