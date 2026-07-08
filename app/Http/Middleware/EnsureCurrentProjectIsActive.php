<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Support\CurrentProject;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentProjectIsActive
{
    public function __construct(private CurrentProject $currentProject)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $project = $this->currentProject->resolve($request);

        abort_unless($project, 404);

        return $next($request);
    }
}
