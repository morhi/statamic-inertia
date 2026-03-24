<?php

namespace App\Support\Transformers;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image;
use Statamic\Fields\Value;

class AssetsTransformer implements FieldTransformerInterface
{
    private const WIDTHS = [480, 960, 1440, 1920];

    public function transform(Value $value): array|null
    {
        $raw    = $value->value();
        $single = $value->fieldtype()->config('max_files') === 1;

        $result = collect($raw instanceof Asset ? [$raw] : ($raw ?? []))
            ->map(fn ($asset) => [
                'url'    => Image::manipulate($asset)->format('webp')->width(1920)->quality(85)->build(),
                'srcset' => collect(self::WIDTHS)
                    ->map(fn ($w) => Image::manipulate($asset)->format('webp')->width($w)->quality(85)->build() . " {$w}w")
                    ->implode(', '),
                'alt'    => $asset->get('alt') ?? '',
            ])
            ->values();

        return $single ? $result->first() : $result->all();
    }
}
