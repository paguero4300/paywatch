<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Pages;

use App\Filament\SuperAdmin\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Crear Empresa')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
            $this->getCancelFormAction()
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('gray'),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Empresa creada exitosamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
