<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    /**
     * Log all state-changing requests (POST, PUT, PATCH, DELETE) automatically.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit state-changing methods on successful responses
        if (
            in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) &&
            $response->isSuccessful()
        ) {
            $this->logRequest($request);
        }

        return $response;
    }

    private function logRequest(Request $request): void
    {
        try {
            // Derive entity type from the route
            $routeName = $request->route()?->getName() ?? '';
            $action = match ($request->method()) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => $request->method(),
            };

            // Try to extract entity type and ID from route parameters
            $entityType = $this->extractEntityType($routeName);
            $entityId = $this->extractEntityId($request);

            // Don't log sensitive payloads (login, MFA)
            $sensitiveRoutes = ['auth.login', 'auth.logout', 'mfa'];
            $isSensitive = false;
            foreach ($sensitiveRoutes as $route) {
                if (str_contains($routeName, $route)) {
                    $isSensitive = true;
                    break;
                }
            }

            AuditService::log(
                action: $action,
                entityType: $entityType,
                entityId: $entityId,
                newValues: $isSensitive ? null : $request->except(['password', 'mfa_code', 'mfa_secret']),
                request: $request,
            );
        } catch (\Throwable) {
            // Never let audit failures break the request
        }
    }

    private function extractEntityType(string $routeName): string
    {
        // e.g. api.v1.stores.store → stores
        $parts = explode('.', $routeName);

        // Remove common prefixes
        $parts = array_filter($parts, fn ($p) => !in_array($p, ['api', 'v1', 'web'], true));

        return $parts[0] ?? 'unknown';
    }

    private function extractEntityId(\Illuminate\Http\Request $request): ?int
    {
        $params = $request->route()?->parameters() ?? [];

        // Return the first numeric route parameter as the entity ID
        foreach ($params as $value) {
            if (is_numeric($value)) {
                return (int) $value;
            }
            if (is_object($value) && method_exists($value, 'getKey')) {
                return $value->getKey();
            }
        }

        return null;
    }
}
