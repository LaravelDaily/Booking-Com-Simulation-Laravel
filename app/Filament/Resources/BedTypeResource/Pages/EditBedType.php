<?php

namespace App\Filament\Resources\BedTypeResource\Pages;

use App\Filament\Resources\BedTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBedType extends EditRecord
{
    protected static string $resource = BedTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
