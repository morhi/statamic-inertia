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
            'blog',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Globals
    |--------------------------------------------------------------------------
    |
    | Whitelist of Global Set vars exposed to every Inertia page as the `globals`
    | shared prop. See addons/morhi/statamic-inertia/INERTIA.md#globals.
    |
    */

    'globals' => [

        // Header site name — catch-all, unrestricted (single var today, but any
        // future var added to the "general" set is exposed the same way).
        'general' => [
            '*',
        ],

        // Footer.
        'footer' => [
            'company_name',
            'company_address',
            'social_links' => \App\Support\Globals\SocialLinksTransformer::class,
            // Newsletter nudge only shown while reading a blog post, not on
            // portfolio/service pages where it would be noise.
            'newsletter_label' => ['collection:blog'],
            'newsletter_cta_url' => ['collection:blog'],
        ],

    ],

];
