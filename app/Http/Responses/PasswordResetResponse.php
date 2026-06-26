<?php

declare(strict_types = 1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Fortify;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(protected string $status)
    {
    }

    public function toResponse($request)
    {
        $message = $request->boolean('setup')
            ? 'Senha cadastrada com sucesso.'
            : trans($this->status);

        return $request->wantsJson()
            ? new JsonResponse(['message' => $message], 200)
            : redirect(Fortify::redirects('password-reset', config('fortify.views', true) ? route('login') : null))
                ->with('status', $message);
    }
}
