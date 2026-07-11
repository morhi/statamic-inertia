<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    | You can specify a custom SSR bundle path, or omit it to let Inertia
    | try and automatically detect it for you.
    |
    | Do note that enabling these options will NOT automatically make SSR work,
    | as a separate rendering service needs to be available. To learn more,
    | please visit https://inertiajs.com/server-side-rendering
    |
    */

    'ssr' => [

        'enabled' => (bool) env('INERTIA_SSR_ENABLED', true),

        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        'ensure_bundle_exists' => (bool) env('INERTIA_SSR_ENSURE_BUNDLE_EXISTS', true),

        // 'bundle' => base_path('bootstrap/ssr/ssr.mjs'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Set `ensure_pages_exist` to true if you want to enforce that Inertia page
    | components exist on disk when rendering a page. This is useful for
    | catching missing or misnamed components.
    |
    | The `page_paths` and `page_extensions` options define where to look
    | for page components and which file extensions to consider.
    |
    */

    'ensure_pages_exist' => false,

    'page_paths' => [

        resource_path('js/Pages'),

    ],

    'page_extensions' => [

        'js',
        'jsx',
        'svelte',
        'ts',
        'tsx',
        'vue',

    ],

    'use_script_element_for_initial_page' => (bool) env('INERTIA_USE_SCRIPT_ELEMENT_FOR_INITIAL_PAGE', false),

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem. For instance, when using `assertInertia`, the assertion
    | attempts to locate the component as a file relative to any of the
    | paths AND with any of the extensions specified here.
    |
    | Note: In a future release, the `page_paths` and `page_extensions`
    | options below will be removed. The root-level options above
    | will be used for both application and testing purposes.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [

            resource_path('js/Pages'),

        ],

        'page_extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

    'history' => [

        'encrypt' => (bool) env('INERTIA_ENCRYPT_HISTORY', false),

    ],

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
