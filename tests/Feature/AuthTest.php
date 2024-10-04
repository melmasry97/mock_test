<?php

use function Pest\Laravel\{post, get};

it('can access the login page', function () {
    get('/login')
        ->assertStatus(200);
});

it('can log in with valid credentials', function () {
    // Create a user
    $user = \App\Models\User::factory()->create([
        'password' => bcrypt($password = 'password123'),
    ]);

    // Attempt to log in
    post('/login', [
        'email' => $user->email,
        'password' => $password,
    ])
    ->assertRedirect('/dashboard');

    // Assert the user is authenticated
    $this->assertAuthenticatedAs($user);
});

it('cannot log in with invalid credentials', function () {
    post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ])
    ->assertSessionHasErrors();
});
