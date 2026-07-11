<?php

namespace App\Support\Globals;

use Morhi\StatamicInertia\Support\Globals\GlobalValueTransformer;

class SocialLinksTransformer implements GlobalValueTransformer
{
    public function transform(mixed $value): mixed
    {
        return collect($value)->map(fn (array $link) => [
            ...$link,
            'icon'     => strtolower($link['platform'] ?? ''),
            'external' => str_starts_with($link['url'] ?? '', 'http'),
        ])->all();
    }
}
