<?php

namespace Morhi\StatamicInertia\Listeners;

use Morhi\StatamicInertia\Http\Middleware\InertiaJsonCache;
use Statamic\Events\UrlInvalidated;

class InvalidateInertiaJsonCache
{
    public function handle(UrlInvalidated $event): void
    {
        $path = parse_url($event->url, PHP_URL_PATH) ?? '/';
        $file = InertiaJsonCache::filePath($path);

        if (file_exists($file)) {
            unlink($file);
        }
    }
}
