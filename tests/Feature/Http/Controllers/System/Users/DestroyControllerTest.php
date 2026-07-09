<?php

declare(strict_types = 1);

use App\Models\User;

test('guests are redirected when deactivating users', function () {
    $user = User::factory()->create();

    $this->delete(route('system.users.destroy', $user))->assertRedirect(route('login'));
});

test('authenticated users without permission cannot deactivate users', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)->delete(route('system.users.destroy', $otherUser))->assertForbidden();

    $this->assertNotSoftDeleted($otherUser);
});

test('authenticated users can deactivate users', function () {
    $user      = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('system.users.destroy', $otherUser))
        ->assertRedirectToRoute('system.users.index')
        ->assertToast('success', 'Usuário inativado.');

    $this->assertSoftDeleted($otherUser);
});
