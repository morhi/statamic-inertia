<?php

namespace Morhi\StatamicInertia\Support\DataProviders;

use Morhi\StatamicInertia\Support\EntryTransformer;
use Statamic\Contracts\Entries\Entry;

abstract class AbstractEntryData implements EntryDataInterface
{
    public function __construct(protected EntryTransformer $transformer) {}

    protected function baseTransform(Entry $entry, array $fields = []): array
    {
        return $this->transformer->transform($entry, $fields);
    }
}
