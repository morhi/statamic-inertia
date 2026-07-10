<?php

namespace Morhi\StatamicInertia\Fieldtypes;

use Statamic\Facades\Collection;
use Statamic\Fieldtypes\Select;

use function Statamic\trans as __;

class EntryListingCollection extends Select
{
    protected $categories = ['controls'];

    // No dedicated Vue component ships for this fieldtype's handle; reuse Select's.
    protected $component = 'select';

    protected function getOptions(): array
    {
        return collect(config('inertia.entry_listing.allowed_collections', []))
            ->map(fn ($handle) => [
                'value' => $handle,
                'label' => optional(Collection::findByHandle($handle))->title() ?? $handle,
            ])
            ->values()
            ->all();
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                        'type' => 'text',
                        'default' => '',
                        'width' => '50',
                    ],
                    'clearable' => [
                        'display' => __('Clearable'),
                        'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                        'type' => 'toggle',
                        'default' => false,
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                ],
            ],
        ];
    }
}
