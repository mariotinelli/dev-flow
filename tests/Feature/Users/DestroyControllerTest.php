<?php

declare(strict_types = 1);

use App\Models\User;

test('authenticated users can deactivate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('users.destroy', $otherUser))
        ->assertRedirectToRoute('users.index')
        ->assertToast('success', 'Usuário inativado.');

    $this->assertSoftDeleted($otherUser);
});

test('soft deleted user users cannot authenticate', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)->delete(route('users.destroy', $otherUser));

    auth()->logout();

    $this->post(route('login.store'), [
        'email'    => $otherUser->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});
