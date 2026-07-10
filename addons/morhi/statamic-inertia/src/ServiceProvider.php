<?php

namespace Morhi\StatamicInertia;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Morhi\StatamicInertia\Http\Middleware\HandleInertiaRequests;
use Morhi\StatamicInertia\Http\Middleware\InertiaAwareStaticCache;
use Morhi\StatamicInertia\Http\Middleware\InertiaJsonCache;
use Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry;
use Morhi\StatamicInertia\Support\EntryTransformer;
use Morhi\StatamicInertia\Support\Transformers\AssetsTransformer;
use Morhi\StatamicInertia\Support\Transformers\BardTransformer;
use Morhi\StatamicInertia\Support\Transformers\ReplicatorTransformer;
use Morhi\StatamicInertia\Support\Transformers\SelectTransformer;
use Statamic\Http\Middleware\AddViewPaths;
use Statamic\Http\Middleware\HandleToken;
use Statamic\Http\Middleware\Localize;
use Statamic\Http\Middleware\StacheLock;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $middlewareGroups = [
        'statamic.inertia' => [
            InertiaAwareStaticCache::class,
            InertiaJsonCache::class,
            StacheLock::class,
            HandleToken::class,
            Localize::class,
            AddViewPaths::class,
            HandleInertiaRequests::class,
        ],
    ];

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

    public function bootAddon()
    {
        $this->bootConfigFile();
        $this->bootPublishGroups();
        $this->bootInertiaJsonCacheListeners();
    }

    private function bootConfigFile(): void
    {
        $config = __DIR__.'/../config/inertia.php';

        $this->mergeConfigFrom($config, 'inertia');

        $this->publishes([
            $config => config_path('inertia.php'),
        ], 'statamic-inertia-config');
    }

    private function bootPublishGroups(): void
    {
        $stubs = __DIR__.'/../stubs';

        $this->publishes([
            "{$stubs}/js" => resource_path('js'),
        ], 'statamic-inertia-scaffold');

        $this->publishes([
            "{$stubs}/js-examples" => resource_path('js/Blocks'),
            "{$stubs}/fieldsets-examples" => resource_path('fieldsets'),
            "{$stubs}/blueprints-examples" => resource_path('blueprints/collections/pages'),
        ], 'statamic-inertia-examples');

        $this->publishes([
            "{$stubs}/views" => resource_path('views'),
        ], 'statamic-inertia-views');

        $this->publishes([
            "{$stubs}/vite.config.js" => base_path('vite.config.js'),
            "{$stubs}/package.json" => base_path('package.json'),
        ], 'statamic-inertia-project-files');
    }

    private function bootInertiaJsonCacheListeners(): void
    {
        // `FileCacher::flush()` doesn't fire UrlInvalidated — catch it via the artisan commands.
        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            if (in_array($event->command, ['statamic:static:clear', 'cache-tracker:flush'])) {
                File::deleteDirectory(public_path('static/json'));
            }
        });
    }
}
