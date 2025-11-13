<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Pages;

use App\Filament\SuperAdmin\Resources\Companies\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar Empresa')
                ->icon('heroicon-o-trash')
                ->modalHeading('Eliminar empresa')
                ->modalDescription('¿Está seguro de que desea eliminar esta empresa? Esta acción eliminará permanentemente la empresa, todos sus usuarios asociados, dispositivos y notificaciones.')
                ->modalSubmitActionLabel('Sí, eliminar empresa')
                ->color('danger')
                ->requiresConfirmation()
                ->successNotificationTitle('Empresa eliminada exitosamente'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Guardar Cambios')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            $this->getCancelFormAction()
                ->label('Cancelar')
                ->icon('heroicon-o-x-circle')
                ->color('gray'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Empresa actualizada exitosamente';
    }
}
