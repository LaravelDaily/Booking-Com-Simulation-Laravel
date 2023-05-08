<?php

namespace App\Filament\Resources\GeographicalObjectResource\Pages;

use App\Filament\Resources\GeographicalObjectResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeographicalObject extends EditRecord
{
    protected static string $resource = GeographicalObjectResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
