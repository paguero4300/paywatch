<?php

namespace App\Filament\Resources\Devices\Pages;

use App\Filament\Resources\Devices\DeviceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Extraer cashiers si existe
        $cashiers = $data['cashiers'] ?? [];
        unset($data['cashiers']);

        // 2. Crear el registro en 'usuario'
        $record = static::getModel()::create($data);

        // 3. Asociar en la tabla pivote 'company_device'
        $company = Filament::getTenant();
        if ($company) {
            $company->devices()->attach($record);
        }

        // 4. Asignar cajeros si se especificaron
        if (!empty($cashiers)) {
            $record->cashiers()->attach($cashiers);
        }

        return $record;
    }
}
