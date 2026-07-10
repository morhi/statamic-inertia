<?php

namespace Morhi\StatamicInertia\Support\EntryListing;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Thoughtco\StatamicCacheTracker\Events\TrackContentTags;

class EntryListingQuery
{
    private const MAX_PER_PAGE = 50;

    public function __construct(protected EntryListingPreviewRegistry $previews) {}

    public function paginate(string $collectionHandle, int $perPage, int $page): array
    {
        $perPage = min(max($perPage, 1), self::MAX_PER_PAGE);

        $paginator = Entry::query()
            ->where('collection', $collectionHandle)
            ->where('published', true)
            ->where('site', Site::current()->handle())
            ->orderByDesc('date')
            // `date` is nullable/non-unique (e.g. entries created before the collection
            // had `dated: true`), so without a stable tiebreaker two separate page
            // requests can return entries in a different order, causing "load more" to
            // repeat or skip entries. `id` is unique and always present.
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $entries = collect($paginator->items())->map(function ($entry) use ($collectionHandle) {
            $this->trackTags($entry, $collectionHandle);

            return [
                'id'      => $entry->id(),
                'title'   => $entry->get('title'),
                'url'     => $entry->url(),
                'date'    => optional($entry->date())->format('Y-m-d'),
                'preview' => $this->previews->resolve($entry, $collectionHandle),
            ];
        })->all();

        return [
            'entries'   => $entries,
            'next_page' => $paginator->hasMorePages() ? $page + 1 : null,
            'has_more'  => $paginator->hasMorePages(),
        ];
    }

    private function trackTags($entry, string $collectionHandle): void
    {
        if (! class_exists(TrackContentTags::class)) {
            return;
        }

        TrackContentTags::dispatch([
            'collection:'.$collectionHandle,
            $collectionHandle.':'.$entry->id(),
        ]);
    }
}
