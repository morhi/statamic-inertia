<?php

namespace App\Providers;

use App\Support\DataProviders\EntryDataRegistry;
use App\Support\EntryTransformer;
use App\Support\Transformers\AssetsTransformer;
use App\Support\Transformers\BardTransformer;
use App\Support\Transformers\ReplicatorTransformer;
use App\Support\Transformers\SelectTransformer;
use App\Listeners\InvalidateInertiaJsonCache;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Statamic\Events\UrlInvalidated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EntryDataRegistry::class);

        $this->app->singleton(EntryTransformer::class, fn () => new EntryTransformer([
            'assets'     => new AssetsTransformer(),
            'bard'       => new BardTransformer(),
            'replicator' => new ReplicatorTransformer(),
            'select'     => new SelectTransformer(),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce https for production environment
        if (Str::startsWith(config('app.url'), 'https://')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        $this->registerInertiaMiddlewareGroup();
        $this->registerInertiaJsonCacheListeners();
    }

    private function registerInertiaJsonCacheListeners(): void
    {
        Event::listen(UrlInvalidated::class, InvalidateInertiaJsonCache::class);

        // `FileCacher::flush()` doesn't fire UrlInvalidated — catch it via the artisan commands
        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            if (in_array($event->command, ['statamic:static:clear', 'cache-tracker:flush'])) {
                File::deleteDirectory(public_path('static/json'));
            }
        });
    }

    private function registerInertiaMiddlewareGroup(): void
    {
        $this->app->make(Router::class)->middlewareGroup('statamic.inertia', [
            \App\Http\Middleware\InertiaAwareStaticCache::class,
            \App\Http\Middleware\InertiaJsonCache::class,
            \Statamic\Http\Middleware\StacheLock::class,
            \Statamic\Http\Middleware\HandleToken::class,
            \Statamic\Http\Middleware\Localize::class,
            \Statamic\Http\Middleware\AddViewPaths::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    }
}
