<?php

use Illuminate\Support\Facades\Route;
use Morhi\StatamicInertia\Http\Controllers\EntryListingController;
use Morhi\StatamicInertia\Http\Controllers\StatamicPageController;

/*
 * Plain JSON pagination endpoint for the entry_listing block's "Load more" button.
 * Deliberately outside the 'statamic.inertia' middleware group and not an Inertia
 * request, so it never passes through InertiaAwareStaticCache/InertiaJsonCache —
 * always fresh by construction. Mounted under the existing 'api' exclusion below,
 * so it can never collide with the wildcard route.
 */
Route::get('/api/inertia/entry-listing', EntryListingController::class)
    ->name('inertia.entry-listing');

/*
 * Wildcard route that hands every public URL to Statamic's entry resolver.
 * Excludes system paths: cp (Control Panel), api (REST API), up (health check), img (Glide).
 *
 * Component mapping: {StudlyCollection}/{StudlyBlueprint}
 * e.g. /blog/my-post → Entry (collection: blog, blueprint: article) → Blog/Article.vue
 */
Route::middleware('statamic.inertia')
    ->get('/{uri?}', StatamicPageController::class)
    ->where('uri', '^(?!cp(/|$)|api(/|$)|up$|img(/|$)).*')
    ->name('inertia.page');
