<?php

namespace App\Support\DataProviders;

use Statamic\Contracts\Entries\Entry;

class EntryDataRegistry
{
    /** @var array<string, class-string<EntryDataInterface>> */
    private array $byBlueprint = [];

    /** @var array<string, class-string<EntryDataInterface>> */
    private array $byEntry = [];

    /** @param class-string<EntryDataInterface> $class */
    public function forBlueprint(string $handle, string $class): void
    {
        $this->byBlueprint[$handle] = $class;
    }

    /** @param class-string<EntryDataInterface> $class */
    public function forEntry(string $idOrSlug, string $class): void
    {
        $this->byEntry[$idOrSlug] = $class;
    }

    public function resolve(Entry $entry): ?EntryDataInterface
    {
        $class = $this->byEntry[$entry->id()]
            ?? $this->byEntry[$entry->slug()]
            ?? $this->byBlueprint[$entry->blueprint()->handle()]
            ?? null;

        return $class ? app($class) : null;
    }
}
