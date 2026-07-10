<?php

namespace Morhi\StatamicInertia\Support\EntryListing;

use Statamic\Contracts\Entries\Entry;

class EntryListingPreviewRegistry
{
    /** @var array<string, class-string<EntryListingPreviewInterface>> */
    private array $byCollection = [];

    /** @param class-string<EntryListingPreviewInterface> $class */
    public function forCollection(string $handle, string $class): void
    {
        $this->byCollection[$handle] = $class;
    }

    public function resolve(Entry $entry, string $collectionHandle): array
    {
        $class = $this->byCollection[$collectionHandle] ?? null;

        return $class ? app($class)->preview($entry) : [];
    }
}
