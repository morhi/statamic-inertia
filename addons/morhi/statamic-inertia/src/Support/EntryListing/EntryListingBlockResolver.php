<?php

namespace Morhi\StatamicInertia\Support\EntryListing;

use Illuminate\Support\Arr;
use Morhi\StatamicInertia\Support\Blocks\BlockResolverInterface;
use Statamic\Fields\Value;
use Statamic\Fields\Values;

class EntryListingBlockResolver implements BlockResolverInterface
{
    public function __construct(protected EntryListingQuery $query) {}

    public function resolve(Values $set): array
    {
        // The `entry_listing_collection` fieldtype's augmented value is a LabeledValue,
        // not the handle string — we need the raw stored value here instead, the
        // same way Values::toArray() unwraps it via ->raw().
        $rawCollection = $set->getProxiedInstance()->get('collection');
        $rawCollection = $rawCollection instanceof Value ? $rawCollection->raw() : $rawCollection;
        $collectionHandle = Arr::first(Arr::wrap($rawCollection));

        // Plain scalar fieldtypes (integer, text) don't need raw unwrapping —
        // their augmented value already is the plain value.
        $perPage = (int) ($set->per_page ?: 6);

        $result = $this->query->paginate($collectionHandle, $perPage, 1);

        return array_merge([
            'heading'       => $set->heading,
            'collection'    => $collectionHandle,
            'per_page'      => $perPage,
            'load_more_url' => '/api/inertia/entry-listing',
        ], $result);
    }
}
