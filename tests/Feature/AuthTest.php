<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('registration fails with admin role', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_ADMINISTRATOR
    ]);

    $response->assertStatus(422);
});

test('registration succeeds with owner role', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_OWNER
    ]);

    $response->assertStatus(200)->assertJsonStructure([
        'access_token',
    ]);
});

test('registration succeeds with user role', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Valid name',
        'email' => 'valid@email.com',
        'password' => 'ValidPassword',
        'password_confirmation' => 'ValidPassword',
        'role_id' => Role::ROLE_USER
    ]);

    $response->assertStatus(200)->assertJsonStructure([
        'access_token',
    ]);
});
