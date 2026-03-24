<?php

use App\Http\Controllers\StatamicPageController;
use Illuminate\Support\Facades\Route;

/*
 * Wildcard route that hands every public URL to Statamic's entry resolver.
 * Excludes system paths: cp (Control Panel), api (REST API), up (health check).
 *
 * Component mapping: {StudlyCollection}/{StudlyBlueprint}
 * e.g. /blog/my-post → Entry (collection: blog, blueprint: article) → Blog/Article.vue
 */
Route::get('/{uri?}', StatamicPageController::class)
    ->where('uri', '^(?!cp(/|$)|api(/|$)|up$|img(/|$)).*')
    ->name('inertia.page');
