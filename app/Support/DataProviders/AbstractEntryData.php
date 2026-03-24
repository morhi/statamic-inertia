<?php

namespace App\Support\DataProviders;

use App\Support\EntryTransformer;
use Statamic\Contracts\Entries\Entry;

abstract class AbstractEntryData implements EntryDataInterface
{
    public function __construct(protected EntryTransformer $transformer) {}

    protected function baseTransform(Entry $entry, array $fields = []): array
    {
        return $this->transformer->transform($entry, $fields);
    }
}
