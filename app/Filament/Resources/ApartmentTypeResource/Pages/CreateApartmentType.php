<?php

namespace App\Filament\Resources\ApartmentTypeResource\Pages;

use App\Filament\Resources\ApartmentTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApartmentType extends CreateRecord
{
    protected static string $resource = ApartmentTypeResource::class;
}
