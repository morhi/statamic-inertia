<?php

namespace Morhi\StatamicInertia\Http\Middleware;

class InertiaAwareStaticCache extends \Statamic\StaticCaching\Middleware\Cache
{
    public function handle($request, \Closure $next)
    {
        // Inertia partial reloads return JSON — skip static caching entirely
        // to prevent JSON being written as .html files on disk.
        if ($request->header('X-Inertia')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
