<?php

namespace Morhi\StatamicInertia\Http\Controllers;

use Illuminate\Http\Request;
use Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry;
use Morhi\StatamicInertia\Support\EntryTransformer;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Structures\Page;

class StatamicPageController extends InertiaController
{
    public function __construct(
        private EntryTransformer $transformer,
        private EntryDataRegistry $registry,
    ) {}

    /**
     * Resolve any URL to a Statamic entry and render the matching Inertia page component.
     *
     * Component naming convention: {StudlyCollection}/{StudlyBlueprint}
     * e.g. collection "pages", blueprint "page"    → Pages/Page.vue
     *      collection "blog",  blueprint "article" → Blog/Article.vue
     */
    public function __invoke(Request $request, ?string $uri = null)
    {
        $uri   = '/' . ltrim($uri ?? '', '/');
        $entry = Entry::findByUri($uri, Site::current()->handle());

        abort_unless($entry, 404);

        // For structured collections, findByUri returns a Page proxy.
        // Unwrap it to get the underlying Entry so collection() and blueprint() work correctly.
        if ($entry instanceof Page) {
            $entry = $entry->entry();
            abort_unless($entry, 404);
        }

        return $this->view($this->resolveComponent($entry), [
            'entry' => [
                'id'         => $entry->id(),
                'url'        => $entry->url(),
                'slug'       => $entry->slug(),
                'collection' => $entry->collection()->handle(),
                'blueprint'  => $entry->blueprint()->handle(),
                'data'       => $this->resolveData($entry),
            ],
        ]);
    }

    private function resolveData(EntryContract $entry): array
    {
        $provider = $this->registry->resolve($entry);

        return $provider
            ? $provider->transform($entry)
            : $this->transformer->transform($entry);
    }
}
