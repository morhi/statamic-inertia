<?php

namespace Morhi\StatamicInertia\Support\Transformers;

use Morhi\StatamicInertia\Support\EntryTransformer;
use Statamic\Fields\Value;
use Statamic\Fields\Values;

class ReplicatorTransformer implements FieldTransformerInterface
{
    public function transform(Value $value): array
    {
        return collect($value->value())
            ->map(function (Values $set) {
                return $set->getProxiedInstance()
                    ->keys()
                    ->mapWithKeys(function ($key) use ($set) {
                        $val = $set->getProxiedInstance()->get($key);

                        if ($val instanceof Value) {
                            return [$key => app(EntryTransformer::class)->transformValue($val)];
                        }

                        return [$key => $set->offsetGet($key)];
                    })
                    ->all();
            })
            ->values()
            ->all();
    }
}
