<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Statamic\Facades\Cascade;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     * This is an Antlers template — Blade directives are injected via partials.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    protected $rootView = 'layout';

    public function handle(Request $request, Closure $next): mixed
    {
        return tap(parent::handle($request, $next), function ($response) {
            // Prevent the browser from caching Inertia JSON responses. Without this the browser
            // may serve a cached JSON payload when the user expects a full HTML page (e.g. back button).
            if ($response->headers->has('X-Inertia')) {
                $response->setCache(['no_store' => true]);
            }
        });
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        // Hydrate Cascade so Statamic globals are available in Antlers templates.
        Cascade::instance()->hydrate();

        $site = Site::current() ?? Site::default();

        $nav = Inertia::once(function () use ($site) {
            return Nav::findByHandle('main')
                ?->in($site->handle())
                ?->flattenedPages()
                ->map(fn($page) => [
                    'label' => $page->title(),
                    'href'  => $page->url(),
                ])
                ->values()
                ->all() ?? [];
        });

        return array_merge([
            'site'   => $site->handle(),
            'locale' => $site->shortLocale(),
            'nav'    => $nav,
        ], parent::share($request));
    }
}
