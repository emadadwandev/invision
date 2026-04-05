<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized.');
        }

        $userRole = $request->user()->role;

        foreach ($roles as $role) {
            $enumRole = UserRole::tryFrom($role);
            if ($enumRole && $userRole === $enumRole) {
                return $next($request);
            }
        }

        abort(403, 'You do not have the required role to access this resource.');
    }
}
