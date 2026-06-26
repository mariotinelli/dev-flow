<?php

declare(strict_types = 1);

use App\Models\User;

test('authenticated users can activate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->trashed()->create();

    $this->actingAs($user)
        ->post(route('users.activate', $otherUser))
        ->assertRedirectToRoute('users.index')
        ->assertToast('success', 'Usuário ativado.');

    $this->assertNotSoftDeleted($otherUser);
});
