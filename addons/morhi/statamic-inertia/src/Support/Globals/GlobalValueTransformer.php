<?php

namespace Morhi\StatamicInertia\Support\Globals;

interface GlobalValueTransformer
{
    public function transform(mixed $value): mixed;
}
