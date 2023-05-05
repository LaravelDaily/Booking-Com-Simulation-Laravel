<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getSubheading(): string|Htmlable|null
    {
        return 'This form will create an administrator user';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = Role::ROLE_ADMINISTRATOR;

        return $data;
    }
}
