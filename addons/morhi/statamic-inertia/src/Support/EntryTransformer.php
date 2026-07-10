<?php

namespace Morhi\StatamicInertia\Support;

use Morhi\StatamicInertia\Support\Transformers\FieldTransformerInterface;
use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Value;

class EntryTransformer
{
    /** @param array<string, FieldTransformerInterface> $transformers fieldtype => transformer */
    public function __construct(protected array $transformers = []) {}

    public function transformValue(Value $value): mixed
    {
        $handle = $value->fieldtype()->handle();

        if (isset($this->transformers[$handle])) {
            return $this->transformers[$handle]->transform($value);
        }

        return $value->value();
    }

    public function transform(Entry $entry, array $fields = []): array
    {
        $blueprint = $entry->blueprint();
        $data      = empty($fields) ? $entry->data() : $entry->data()->only($fields);

        return $data->mapWithKeys(function ($rawValue, string $handle) use ($entry, $blueprint) {
                $field = $blueprint->field($handle);

                if ($field && isset($this->transformers[$field->type()])) {
                    return [$handle => $this->transformers[$field->type()]->transform($entry->augmentedValue($handle))];
                }

                return [$handle => $rawValue];
            })
            ->all();
    }
}
