<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->tenant_id) {
            app()->instance('current_tenant_id', $request->user()->tenant_id);
        }

        return $next($request);
    }
}
