<?php

namespace App\Filament\Resources\RoomTypeResource\Pages;

use App\Filament\Resources\RoomTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomTypes extends ListRecords
{
    protected static string $resource = RoomTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
