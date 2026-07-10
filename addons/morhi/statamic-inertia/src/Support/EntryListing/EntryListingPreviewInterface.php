<?php

namespace Morhi\StatamicInertia\Support\EntryListing;

use Statamic\Contracts\Entries\Entry;

interface EntryListingPreviewInterface
{
    /**
     * Extra fields shown on this collection's entry-listing card, beyond the
     * baseline id/title/url/date every listed entry already gets.
     */
    public function preview(Entry $entry): array;
}
