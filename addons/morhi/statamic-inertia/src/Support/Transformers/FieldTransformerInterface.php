<?php

namespace Morhi\StatamicInertia\Support\Transformers;

use Statamic\Fields\Value;

interface FieldTransformerInterface
{
    public function transform(Value $value): mixed;
}
