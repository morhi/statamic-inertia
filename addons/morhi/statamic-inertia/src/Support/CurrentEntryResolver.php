<?php

namespace Morhi\StatamicInertia\Support;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Structures\Page;

class CurrentEntryResolver
{
    /**
     * Resolve the request's URI to a Statamic entry, unwrapping the Page proxy that
     * findByUri() returns for structured collections. Shared by StatamicPageController
     * (to render the page) and HandleInertiaRequests (to scope the globals prop),
     * so both always agree on what "the current entry" is.
     */
    public function resolve(Request $request): ?EntryContract
    {
        $uri = '/' . ltrim($request->route('uri') ?? '', '/');
        $entry = Entry::findByUri($uri, Site::current()->handle());

        if (! $entry) {
            return null;
        }

        if ($entry instanceof Page) {
            $entry = $entry->entry();
        }

        return $entry;
    }
}
