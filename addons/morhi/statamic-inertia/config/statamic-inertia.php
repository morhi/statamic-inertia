<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entry Listing Block
    |--------------------------------------------------------------------------
    |
    | Configuration for the "entry_listing" page-builder block's "Load more"
    | pagination endpoint. This route is deliberately plain (non-Inertia) so
    | it's never touched by the static/JSON caching layers.
    |
    */

    'entry_listing' => [

        // Collection handles this public endpoint is allowed to query and expose.
        // New collections must be added here explicitly (defense against
        // accidentally exposing internal-only collections like "orders").
        'allowed_collections' => [
            //
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Globals
    |--------------------------------------------------------------------------
    |
    | Whitelist of Global Set vars exposed to every Inertia page as the `globals`
    | shared prop. Nothing reaches Vue unless explicitly listed here (defense
    | against a global added for one page silently leaking into every other
    | page's props simply because it exists).
    |
    | Each global set handle maps to a mixed list of rules:
    | - a bare var handle (or '*') exposes that var (or, for '*', any var not
    |   explicitly listed) unrestricted;
    | - 'var' => ['type:value', ...] exposes it only when every scope predicate
    |   matches (AND). Supported predicates: 'site:', 'collection:', 'blueprint:';
    | - 'var' => SomeTransformer::class exposes it unrestricted, and reshapes
    |   the value (already run through the normal fieldtype transformers) via
    |   a class implementing Morhi\StatamicInertia\Support\Globals\GlobalValueTransformer.
    |
    | 'site:' restricts which pages a var is exposed on — it's unrelated to a
    | field's own `localizable: true`, which already varies its value per site.
    |
    | 'site_settings' => [
    |     '*',                                       // catch-all: any var not listed below, unrestricted
    |     'back_to_blog' => ['collection:blog'],      // only exposed on pages in the "blog" collection
    |     'links' => FooterLinksTransformer::class,   // reshaped after the normal fieldtype transform
    | ],
    |
    */

    'globals' => [
        //
    ],

];
