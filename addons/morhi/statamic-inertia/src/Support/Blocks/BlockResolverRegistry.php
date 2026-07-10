<?php

namespace Morhi\StatamicInertia\Support\Blocks;

class BlockResolverRegistry
{
    /** @var array<string, class-string<BlockResolverInterface>> */
    private array $bySetHandle = [];

    /** @param class-string<BlockResolverInterface> $class */
    public function forSet(string $handle, string $class): void
    {
        $this->bySetHandle[$handle] = $class;
    }

    public function resolve(string $setHandle): ?BlockResolverInterface
    {
        $class = $this->bySetHandle[$setHandle] ?? null;

        return $class ? app($class) : null;
    }
}
