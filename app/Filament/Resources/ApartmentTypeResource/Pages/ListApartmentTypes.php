<?php

namespace App\Filament\Resources\ApartmentTypeResource\Pages;

use App\Filament\Resources\ApartmentTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApartmentTypes extends ListRecords
{
    protected static string $resource = ApartmentTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
