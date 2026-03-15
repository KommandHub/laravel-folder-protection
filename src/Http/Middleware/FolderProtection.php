<?php

declare(strict_types=1);

namespace KommandHub\FolderProtection\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FolderProtection
{
    /**
     * Handle an incoming request.
     *
     * If folder protection is enabled and valid credentials are configured,
     * the request must supply matching HTTP Basic Auth credentials to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isProtectionActive()) {
            if (! $this->isAuthenticated($request)) {
                return $this->unauthorizedResponse();
            }
        }

        return $next($request);
    }

    /**
     * Determine whether folder protection is enabled and credentials are configured.
     */
    private function isProtectionActive(): bool
    {
        return config('folder-protection.enabled')
            && ! empty(config('folder-protection.user'))
            && ! empty(config('folder-protection.password'));
    }

    /**
     * Check whether the request carries valid Basic Auth credentials.
     */
    private function isAuthenticated(Request $request): bool
    {
        [$requestUser, $requestPassword] = $this->resolveCredentials($request);

        return $requestUser === config('folder-protection.user')
            && $requestPassword === config('folder-protection.password');
    }

    /**
     * Resolve the Basic Auth credentials from the request.
     *
     * Laravel's getUser() / getPassword() may return null when the server does
     * not populate PHP_AUTH_* variables (e.g. behind some reverse proxies).
     * In that case we fall back to parsing the raw Authorization header.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveCredentials(Request $request): array
    {
        $user = $request->getUser();
        $password = $request->getPassword();

        // Fall back to manually decoding the Authorization header when the
        // server abstraction does not expose the credentials directly.
        if (is_null($user) && $request->hasHeader('Authorization')) {
            [$user, $password] = $this->parseBasicAuthHeader(
                $request->header('Authorization')
            ) ?? [null, null];
        }

        return [$user, $password];
    }

    /**
     * Parse a "Basic <base64>" Authorization header value.
     *
     * Returns a [user, password] tuple on success, or null if the header is
     * not a valid Basic Auth header.
     *
     * @return array{0: string, 1: string}|null
     */
    private function parseBasicAuthHeader(string $header): ?array
    {
        if (! str_starts_with($header, 'Basic ')) {
            return null;
        }

        $decoded = base64_decode(substr($header, 6));

        if (! $decoded || ! str_contains($decoded, ':')) {
            return null;
        }

        return explode(':', $decoded, 2);
    }

    /**
     * Build the 401 Unauthorized response that prompts the browser for credentials.
     */
    private function unauthorizedResponse(): Response
    {
        return response('Unauthorized.', 401, [
            'WWW-Authenticate' => 'Basic realm="Protected Area"',
        ]);
    }
}
