<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Enums\ContractType;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
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
        return Inertia::render('developers/Create', [
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
        ]);
    }
}
