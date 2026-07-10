<?php

namespace App\Support\Transformers;

use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;

class SelectTransformer implements FieldTransformerInterface
{
    public function transform(Value $value): mixed
    {
        $raw = $value->value();

        if ($raw instanceof LabeledValue) {
            return $raw->value();
        }

        if (is_array($raw)) {
            return collect($raw)->map(fn($item) => $item instanceof LabeledValue ? $item->value() : $item)->all();
        }

        return $raw;
    }
}
