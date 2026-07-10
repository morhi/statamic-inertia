<?php

namespace Morhi\StatamicInertia\Http\Middleware;

use Morhi\StatamicInertia\Support\Ssr\SsrTrackingGateway;

class InertiaAwareStaticCache extends \Statamic\StaticCaching\Middleware\Cache
{
    public function handle($request, \Closure $next)
    {
        // Inertia partial reloads return JSON — skip static caching entirely
        // to prevent JSON being written as .html files on disk.
        if ($request->header('X-Inertia')) {
            $response = $next($request);
            $response->headers->set('X-Cache-Status', $this->cacheStatusForUncachedResponse($request));

            return $response;
        }

        return parent::handle($request, function ($request) use ($next) {
            $response = $next($request);

            // The document can be rewritten by the static cache at any time (content change,
            // manual clear, etc.), so the browser must never cache it either — only nginx's
            // and Statamic's server-side static cache should be treated as authoritative.
            // Mirrors the no-store already set on Inertia JSON responses (HandleInertiaRequests)
            // and on the nginx @static block that serves this same document from disk.
            $response->headers->set('Cache-Control', 'no-store');

            // If SSR was configured to run but the gateway couldn't reach it (server down,
            // unreachable, erroring), the response was served without pre-rendered markup.
            // Mark it uncacheable so Statamic's own Cache::shouldBeCached() skips it — caching
            // a degraded page would otherwise keep serving it to everyone until the cache is
            // next invalidated, even after SSR comes back.
            $gateway = app(\Inertia\Ssr\Gateway::class);

            if ($gateway instanceof SsrTrackingGateway && $gateway->dispatchFailed()) {
                $response->headers->set('X-Statamic-Uncacheable', true);
            }

            $response->headers->set('X-Cache-Status', $this->cacheStatusForUncachedResponse($request));

            return $response;
        });
    }

    /**
     * X-Cache-Status: HIT is set by nginx itself, only when it serves a cached file
     * directly from disk (see .ddev/nginx_full/nginx-site.conf) — that's the one path
     * guaranteed not to involve a further internal redirect, which is what makes nginx
     * variables/add_header unreliable for the other branches (a `try_files ... /index.php`
     * fallback re-enters nginx's rewrite phase from scratch). Reaching this middleware at
     * all means nginx didn't have a cached copy, so it's always MISS or BYPASS.
     */
    private function cacheStatusForUncachedResponse($request): string
    {
        if ($request->method() !== 'GET' || $request->isLivePreview()) {
            return 'BYPASS';
        }

        return 'MISS';
    }
}
