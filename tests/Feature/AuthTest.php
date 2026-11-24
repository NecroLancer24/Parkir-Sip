<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('guests cannot run dashboard maintenance actions', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/hapus-data-coba', '/randomize-slot', '/setup-denah-3-lantai']);

test('the login page can be rendered', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Selamat datang kembali');
});

test('the register page can be rendered', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Buat ruang kerja Anda');
});

test('an operator can sign in', function () {
    $user = User::factory()->create(['password' => 'password']);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('invalid credentials do not authenticate', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a new operator can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Alex Morgan',
        'organization' => 'Central Park',
        'email' => 'alex@centralpark.co',
        'password' => 'password123',
        'terms' => '1',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name' => 'Alex Morgan',
        'organization' => 'Central Park',
        'email' => 'alex@centralpark.co',
    ]);
});

test('an operator can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
