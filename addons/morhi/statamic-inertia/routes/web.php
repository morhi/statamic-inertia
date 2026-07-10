<?php

use Illuminate\Support\Facades\Route;
use Morhi\StatamicInertia\Http\Controllers\StatamicPageController;

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
