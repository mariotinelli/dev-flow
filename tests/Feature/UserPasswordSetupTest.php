<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\UserPasswordSetupNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('password setup screen can be rendered from notification link', function () {
    $user  = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $response->assertOk();
});

test('password setup notification uses account creation copy and reset token link', function () {
    $user         = User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    $notification = new UserPasswordSetupNotification('token-123');
    $mail         = $notification->toMail($user);

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($mail->subject)->toBe('Crie sua senha - ' . config('app.name'))
        ->and($mail->greeting)->toBe('Olá Ada Lovelace!')
        ->and($mail->introLines)->toContain('Uma conta foi criada para você no sistema ' . config('app.name') . '.')
        ->and($mail->actionText)->toBe('Cadastrar minha senha')
        ->and($mail->actionUrl)->toContain('/reset-password/token-123')
        ->and($mail->actionUrl)->toContain('email=ada%40example.com')
        ->and($mail->actionUrl)->toContain('setup=1')
        ->and($mail->outroLines)->toContain('Este link expira em 60 minutos.');
});

test('user can define password with valid token', function () {
    $user  = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post(route('password.update'), [
        'token'                 => $token,
        'email'                 => $user->email,
        'password'              => 'new-password',
        'password_confirmation' => 'new-password',
        'setup'                 => '1',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Senha cadastrada com sucesso.');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('user cannot define password with invalid token', function () {
    $user = User::factory()->create();

    $this->post(route('password.update'), [
        'token'                 => 'invalid-token',
        'email'                 => $user->email,
        'password'              => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors('email');
});
