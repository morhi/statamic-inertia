<?php

namespace App\Support\DataProviders;

use Statamic\Contracts\Entries\Entry;

interface EntryDataInterface
{
    public function transform(Entry $entry): array;
}
