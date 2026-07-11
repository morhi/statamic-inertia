<?php

namespace Morhi\StatamicInertia\Http\Controllers;

use Illuminate\Http\Request;
use Morhi\StatamicInertia\Support\CurrentEntryResolver;
use Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry;
use Morhi\StatamicInertia\Support\EntryTransformer;
use Statamic\Contracts\Entries\Entry as EntryContract;

class StatamicPageController extends InertiaController
{
    public function __construct(
        private EntryTransformer $transformer,
        private EntryDataRegistry $registry,
        private CurrentEntryResolver $entryResolver,
    ) {}

    /**
     * Resolve any URL to a Statamic entry and render the matching Inertia page component.
     *
     * Component naming convention: {StudlyCollection}/{StudlyBlueprint}
     * e.g. collection "pages", blueprint "page"    → Pages/Page.vue
     *      collection "blog",  blueprint "article" → Blog/Article.vue
     */
    public function __invoke(Request $request)
    {
        $entry = $this->entryResolver->resolve($request);

        abort_unless($entry, 404);

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
