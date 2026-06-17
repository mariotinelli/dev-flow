<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Users;

use App\Enums\ContractType;
use App\Enums\JobTitle;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/Create', [
            'jobTitles'     => JobTitle::options(),
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
        ]);
    }
}
