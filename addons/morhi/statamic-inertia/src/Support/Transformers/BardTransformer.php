<?php

namespace Morhi\StatamicInertia\Support\Transformers;

use Statamic\Fields\Value;

class BardTransformer implements FieldTransformerInterface
{
    public function transform(Value $value): string
    {
        return (string) $value;
    }
}
