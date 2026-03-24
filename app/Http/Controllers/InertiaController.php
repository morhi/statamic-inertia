<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

abstract class InertiaController extends Controller
{
    protected function view(string $component, array $data = []): Response
    {
        return Inertia::render($component, $data);
    }

    /**
     * Resolve the Inertia page component name from a Statamic entry.
     * Convention: {StudlyCollection}/{StudlyBlueprint}
     * e.g. collection "blog", blueprint "article" → "Blog/Article"
     *
     * Override this in subclasses to use a custom mapping.
     */
    protected function resolveComponent(\Statamic\Contracts\Entries\Entry $entry): string
    {
        $collection = str($entry->collection()->handle())->studly()->value();
        $blueprint  = str($entry->blueprint()->handle())->studly()->value();

        // When the blueprint and collection share the same name (e.g. pages/pages),
        // use just the collection name to avoid redundant nesting (Pages/Pages.vue).
        if ($collection === $blueprint) {
            return $collection;
        }

        return "{$collection}/{$blueprint}";
    }
}
