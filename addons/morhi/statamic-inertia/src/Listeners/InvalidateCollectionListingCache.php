<?php

namespace Morhi\StatamicInertia\Listeners;

use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Thoughtco\StatamicCacheTracker\Facades\Tracker;

class InvalidateCollectionListingCache
{
    /**
     * Cache-tracker only invalidates a page by the specific {collection}:{id} tag an
     * entry was rendered with, so a brand-new entry (not yet tagged anywhere) would
     * never bust a listing page that shows it. This closes that gap by also
     * invalidating the broader collection:{handle} tag every entry_listing block
     * attaches to a page (see EntryListingQuery::trackTags()).
     */
    public function handle(EntrySaved|EntryDeleted $event): void
    {
        if (! class_exists(Tracker::class)) {
            return;
        }

        Tracker::invalidate(['collection:'.$event->entry->collection()->handle()]);
    }
}
