<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Extraer el rol
        $role = $data['role'] ?? 'cashier';
        unset($data['role']);

        // 2. Crear el registro User
        $user = static::getModel()::create($data);

        // 3. Asociar el usuario con el Tenant actual y guardar el rol en el pivote
        $company = Filament::getTenant();
        if ($company) {
            $company->users()->attach($user, ['role' => $role]);
        }

        return $user;
    }
}
