<?php

declare(strict_types = 1);

namespace App\Notifications;

use App\Enums\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;

#[Queue(QueuePriority::LowPriority)]
#[Tries(3)]
#[Backoff([10, 30, 60, 120])]
class UserPasswordSetupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $token)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Crie sua senha - ' . config('app.name'))
            ->greeting('Olá ' . $notifiable->name . '!')
            ->line('Uma conta foi criada para você no sistema ' . config('app.name') . '.')
            ->line('Para acessar sua conta, clique no botão abaixo e cadastre sua senha.')
            ->action('Cadastrar minha senha', route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->email,
                'setup' => true,
            ]))
            ->line('Este link expira em 60 minutos.')
            ->line('Se você não reconhece este cadastro, ignore este e-mail.')
            ->salutation('Atenciosamente, ' . config('app.name'));
    }
}
