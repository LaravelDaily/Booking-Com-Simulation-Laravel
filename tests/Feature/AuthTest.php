<?php

use App\Models\Role;
use function Pest\Laravel\{postJson};

test('registration fails with admin role', function () {
    postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_ADMINISTRATOR
    ])
        ->assertStatus(422);
});

test('registration succeeds with owner role', function () {
    postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_OWNER
    ])
        ->assertStatus(200)->assertJsonStructure([
            'access_token',
        ]);
});

test('registration succeeds with user role', function () {
    postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_USER
    ])
        ->assertStatus(200)->assertJsonStructure([
            'access_token',
        ]);
});
