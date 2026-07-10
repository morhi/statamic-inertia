<?php

namespace Morhi\StatamicInertia\Support\DataProviders;

use Statamic\Contracts\Entries\Entry;

interface EntryDataInterface
{
    public function transform(Entry $entry): array;
}
