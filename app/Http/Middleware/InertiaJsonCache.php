<?php

namespace App\Http\Middleware;

class InertiaJsonCache
{
    public function handle($request, \Closure $next)
    {
        if (config('statamic.static_caching.strategy') !== 'full' || !$request->header('X-Inertia')) {
            return $next($request);
        }

        $response = $next($request);

        if ($this->shouldCache($request, $response)) {
            $this->write($request, $response);
        }

        return $response;
    }

    private function shouldCache($request, $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (!str_contains($response->headers->get('Content-Type', ''), 'application/json')) {
            return false;
        }

        // Don't cache draft, private, protected, or explicitly uncacheable pages
        foreach (['X-Statamic-Draft', 'X-Statamic-Private', 'X-Statamic-Protected', 'X-Statamic-Uncacheable'] as $header) {
            if ($response->headers->has($header)) {
                return false;
            }
        }

        return true;
    }

    private function write($request, $response): void
    {
        $path = $this->filePath($request->getPathInfo());
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $response->getContent());
    }

    public static function filePath(string $uri): string
    {
        return public_path('static/json') . $uri . '_.json';
    }
}
