<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Developers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeveloperResource;
use App\Models\Developer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('developers/Index', [
            'developers' => DeveloperResource::collection(
                Developer::query()
                    ->with('user')
                    ->latest()
                    ->get()
            )->resolve($request),
        ]);
    }
}
