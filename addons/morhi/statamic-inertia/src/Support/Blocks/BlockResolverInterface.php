<?php

namespace Morhi\StatamicInertia\Support\Blocks;

use Statamic\Fields\Values;

interface BlockResolverInterface
{
    /**
     * Resolve a replicator set's data, replacing the generic per-field transform.
     *
     * @param  Values  $set  The replicator row's raw proxied values (the block's own field config).
     */
    public function resolve(Values $set): array;
}
