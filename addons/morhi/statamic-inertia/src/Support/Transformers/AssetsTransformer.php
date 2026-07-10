<?php

namespace Morhi\StatamicInertia\Support\Transformers;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\QueryBuilder;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\Image;
use Statamic\Fields\Value;

class AssetsTransformer implements FieldTransformerInterface
{
    private const WIDTHS = [480, 960, 1440, 1920];

    public function transform(Value $value): array|null
    {
        $raw = $value->value();
        $single = $value->fieldtype()->config('max_files') === 1;

        $assets = match (true) {
            $raw instanceof Asset => collect([$raw]),
            $raw instanceof QueryBuilder => $raw->get(),
            is_string($raw) => collect([AssetFacade::find($raw)])->filter(),
            is_array($raw) => collect($raw)->map(fn($id) => $id instanceof Asset ? $id : AssetFacade::find($id))->filter(),
            default => collect(),
        };

        if ($assets->isEmpty()) {
            return null;
        }

        $result = $assets->map(fn($asset) => [
            'url' => Image::manipulate($asset)->format('webp')->width(1920)->quality(85)->build(),
            'srcset' => collect(self::WIDTHS)
                ->map(fn($w) => Image::manipulate($asset)->format('webp')->width($w)->quality(85)->build() . " {$w}w")
                ->implode(', '),
            'alt' => $asset->get('alt') ?? '',
        ])
            ->values();

        return $single ? $result->first() : $result->all();
    }
}
