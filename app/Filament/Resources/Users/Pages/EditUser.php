<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Cargar datos antes de llenar el formulario
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord();
        $company = Filament::getTenant();
        $data['role'] = $user->companies()->where('company_id', $company?->id)->first()?->pivot->role;
        return $data;
    }

    // Sobrescribir el método de actualización
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Extraer el rol
        $role = $data['role'] ?? 'cashier';
        unset($data['role']);

        // 2. Actualizar el registro User
        $record->update($data);

        // 3. Actualizar el rol en la tabla pivote
        $company = Filament::getTenant();
        if ($company) {
            $company->users()->updateExistingPivot($record->id, ['role' => $role]);
        }

        return $record;
    }
}
