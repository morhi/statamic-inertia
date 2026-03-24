<?php

namespace App\Support\Transformers;

use Statamic\Fields\Value;

interface FieldTransformerInterface
{
    public function transform(Value $value): mixed;
}
