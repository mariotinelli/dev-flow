<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Enums\ContractType;
use App\Enums\Seniority;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeveloperResource;
use App\Models\Developer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Developer $developer): Response
    {
        $this->authorize('update', $developer);

        $developer->load('user');

        return Inertia::render('developers/Edit', [
            'developer'     => (new DeveloperResource($developer))->resolve($request),
            'contractTypes' => ContractType::options(),
            'seniorities'   => Seniority::options(),
        ]);
    }
}
