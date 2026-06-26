<?php

declare(strict_types = 1);

namespace App\Jobs;

use App\Enums\QueuePriority;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Support\Facades\Password;

#[Queue(QueuePriority::LowPriority)]
class SendPasswordSetupLink implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $email)
    {
    }

    public function handle(): void
    {
        Password::sendResetLink(['email' => $this->email]);
    }
}
