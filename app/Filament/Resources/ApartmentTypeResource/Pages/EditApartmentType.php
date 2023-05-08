<?php

namespace App\Filament\Resources\ApartmentTypeResource\Pages;

use App\Filament\Resources\ApartmentTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApartmentType extends EditRecord
{
    protected static string $resource = ApartmentTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
