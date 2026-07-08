<?php

declare(strict_types = 1);

use App\Models\User;

test('guests are redirected when activating users', function () {
    $user = User::factory()->trashed()->create();

    $this->post(route('users.activate', $user))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot activate users', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->trashed()->create();

    $this->actingAs($user)->post(route('users.activate', $otherUser))->assertForbidden();

    $this->assertSoftDeleted($otherUser);
});

test('authenticated users can activate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->trashed()->create();

    $this->actingAs($user)
        ->post(route('users.activate', $otherUser))
        ->assertRedirectToRoute('users.index')
        ->assertToast('success', 'Usuário ativado.');

    $this->assertNotSoftDeleted($otherUser);
});
