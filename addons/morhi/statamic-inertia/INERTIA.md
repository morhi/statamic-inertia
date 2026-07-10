# Inertia.js Integration

This addon bridges Statamic's PHP backend with a Vue 3 + TypeScript frontend, including full SSR support. Statamic serves as the data layer; Vue handles all rendering.

For installation instructions, see [README.md](README.md). This document covers the architecture in detail.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Request Lifecycle](#request-lifecycle)
3. [Package Structure](#package-structure)
4. [Publish Groups](#publish-groups)
5. [Root Template](#root-template)
6. [Page Component Resolution](#page-component-resolution)
7. [Entry Data & Transformers](#entry-data--transformers)
8. [Block Builder](#block-builder)
9. [Entry Listing Block](#entry-listing-block)
10. [Navigation](#navigation)
11. [Image Handling (Glide)](#image-handling-glide)
12. [Shared Props](#shared-props)
13. [Caching](#caching)
14. [Statamic Live Preview](#statamic-live-preview)
15. [Development Setup](#development-setup)
16. [SSR Setup](#ssr-setup)
17. [Adding a New Page Type](#adding-a-new-page-type)
18. [Adding a New Block](#adding-a-new-block)

---

## Architecture Overview

```
Browser request
    │
    ▼
NGINX (nginx-site.conf)
    │  ─ serves static files from public/
    │  ─ falls back to index.php for all other requests
    │  ─ serves cached JSON from public/static/json/ on Inertia XHR requests
    ▼
Laravel / Statamic
    │
    ├── Full page load → renders layout.antlers.html → @inertia div → SSR HTML
    │
    └── Inertia XHR (navigation) → returns JSON payload
            │
            ▼
        StatamicPageController
            │  ─ findByUri → Entry
            │  ─ resolveComponent → Vue component name
            │  ─ resolveData → transformed entry data
            ▼
        Inertia::render('Pages/Page', { entry: {...} })
            │
            ▼
        Vue (client hydrates SSR HTML)
```

The controller, middleware, and transformers live in this addon (`Morhi\StatamicInertia\*`, autoloaded via Composer). The Vue/JS frontend, root Antlers views, `vite.config.js`, `package.json`, and example blueprints/fieldsets are published into the host project by `vendor:publish` (see [Publish Groups](#publish-groups)), since Vite can only build files that live inside the host project.

---

## Request Lifecycle

### Full Page Load

1. Browser requests `/some-page`
2. NGINX checks for a static HTML file in `public/static/` — serves it if found (Statamic static cache)
3. No static file → PHP handles the request
4. Laravel middleware stack runs: `InertiaAwareStaticCache` → `InertiaJsonCache` → Statamic middleware → `HandleInertiaRequests`
5. `HandleInertiaRequests` hydrates the Statamic Cascade and shares site-wide props (site, locale, nav)
6. `StatamicPageController` resolves the URL to a Statamic entry, transforms its data, and calls `Inertia::render()`
7. Inertia renders the component server-side (via SSR), injects the result into the `@inertia` div and the serialized page data into a `<script>` tag
8. The client receives HTML with the SSR markup already in place and hydrates it

### Inertia Navigation (SPA)

1. User clicks an `<Link>` (Inertia link)
2. Browser sends `GET /next-page` with `X-Inertia: true` header
3. NGINX checks `public/static/json/next-page_.json` — serves it if found (Inertia JSON cache)
4. No cached JSON → PHP handles, same controller path, but Inertia returns only the JSON payload (no full HTML)
5. Vue swaps the page component on the client; the layout persists

---

## Package Structure

```
addons/morhi/statamic-inertia/
├── config/
│   └── inertia.php                        # inertiajs/inertia-laravel config — always merged, publishable
├── src/
│   ├── ServiceProvider.php                # middleware group, route registration, singletons, publish groups
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── InertiaController.php      # Abstract base: view(), resolveComponent()
│   │   │   ├── StatamicPageController.php # Resolves any URL to an Inertia page
│   │   │   └── EntryListingController.php # Plain JSON "load more" pagination endpoint
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php  # Shared props, nav, no-store cache header
│   │       ├── InertiaAwareStaticCache.php # Skips static cache on XHR, marks SSR-failed pages uncacheable, sets X-Cache-Status
│   │       └── InertiaJsonCache.php       # Caches Inertia JSON responses to disk
│   ├── Listeners/
│   │   ├── InvalidateInertiaJsonCache.php # Deletes stale JSON cache on content change (auto-discovered)
│   │   └── InvalidateCollectionListingCache.php # Busts collection:{handle} tag on entry save/delete, closing the "new entry" cache-tracker gap
│   └── Support/
│       ├── EntryTransformer.php           # Transforms Statamic entry fields to JS-safe values
│       ├── DataProviders/
│       │   ├── EntryDataInterface.php     # Contract for custom data providers
│       │   ├── AbstractEntryData.php      # Base class with transformer access
│       │   └── EntryDataRegistry.php      # Maps blueprint/entry → custom data provider
│       ├── Blocks/
│       │   ├── BlockResolverInterface.php # Contract: resolve(Values $set): array
│       │   └── BlockResolverRegistry.php  # Maps a replicator set handle → custom PHP resolver
│       ├── EntryListing/
│       │   ├── EntryListingQuery.php      # Paginated, tag-tracked entry query for a collection
│       │   ├── EntryListingBlockResolver.php # BlockResolverInterface impl backing the entry_listing block
│       │   ├── EntryListingPreviewInterface.php # Contract: preview(Entry): array
│       │   └── EntryListingPreviewRegistry.php  # Maps collection handle → custom preview provider
│       ├── Ssr/
│       │   └── SsrTrackingGateway.php     # Wraps Inertia's SSR gateway to expose per-request success/failure
│       └── Transformers/
│           ├── FieldTransformerInterface.php # Contract: transform(Value): mixed
│           ├── AssetsTransformer.php      # assets fields → { url, srcset, alt }
│           ├── BardTransformer.php        # bard fields → HTML string
│           ├── ReplicatorTransformer.php  # replicator fields → array of sets (or resolver output, see BlockResolverRegistry)
│           └── SelectTransformer.php      # select fields → raw value(s)
├── routes/
│   └── web.php                            # 'load more' JSON route + catch-all route, registers with 'statamic.inertia' middleware
└── stubs/                                 # Source files for each publish() group — copied into the host project
    ├── js/                                # tag: statamic-inertia-scaffold
    │   ├── app.js                         # Client-side Inertia bootstrap (SSR hydration)
    │   ├── ssr.js                         # Server-side Inertia bootstrap
    │   ├── types.d.ts                     # Block type definitions
    │   ├── utils/
    │   │   ├── useInertiaPageProp.ts      # Typed usePage().props accessor
    │   │   └── usePreviewRefresh.ts       # Statamic live preview support
    │   ├── Pages/
    │   │   ├── Layout.vue                 # Persistent layout (header, nav)
    │   │   └── Pages/
    │   │       └── Page.vue               # Default page component
    │   └── Components/
    │       └── Blocks.vue                 # Renders block array by type
    ├── js-examples/                       # tag: statamic-inertia-examples → resources/js/Blocks/
    │   ├── Text.vue, Hero.vue, Quote.vue, CardGrid.vue, Accordion.vue, ImageCaption.vue, MasonryGallery.vue, EntryListing.vue
    ├── entry-listing-examples/            # tag: statamic-inertia-examples → resources/js/Components/EntryPreviews/
    │   └── EntryPreviews/Default.vue      # Fallback preview card for entry_listing block
    ├── fieldsets-examples/                # tag: statamic-inertia-examples → resources/fieldsets/
    │   └── block_builder.yaml, block_*.yaml (incl. block_entry_listing.yaml)
    ├── blueprints-examples/                # tag: statamic-inertia-examples → resources/blueprints/collections/pages/
    │   └── page.yaml
    ├── views/                             # tag: statamic-inertia-views → resources/views/
    │   ├── layout.antlers.html            # Root HTML template
    │   ├── inertia.antlers.html           # Minimal view (body only)
    │   └── partials/
    │       ├── _inertia-head.blade.php    # @inertiaHead directive
    │       └── _inertia-body.blade.php    # @inertia directive
    ├── vite.config.js                     # tag: statamic-inertia-project-files → project root
    └── package.json                       # tag: statamic-inertia-project-files → project root
```

---

## Publish Groups

Everything that must physically live in the host project (Vite can only build files inside the host project — it can't reach into `vendor/`) is delivered via standard Laravel `vendor:publish` tags, registered in `ServiceProvider::bootAddon()`:

| Tag | Destination | Contents |
|---|---|---|
| `statamic-inertia-config` | `config/inertia.php` | `inertiajs/inertia-laravel` config (also auto-merged, so it works even unpublished) |
| `statamic-inertia-scaffold` | `resources/js/` | Core bootstrap: `app.js`, `ssr.js`, `types.d.ts`, `utils/`, `Pages/Layout.vue`, `Pages/Pages/Page.vue`, `Components/Blocks.vue` |
| `statamic-inertia-examples` | `resources/js/Blocks/`, `resources/js/Components/EntryPreviews/`, `resources/fieldsets/`, `resources/blueprints/collections/pages/` | Optional starter blocks (incl. `entry_listing`) + matching fieldsets/blueprint + fallback entry-listing preview card — safe to delete or replace |
| `statamic-inertia-views` | `resources/views/` | Root Antlers template + Blade partials |
| `statamic-inertia-project-files` | `vite.config.js`, `package.json` | Vite config and npm dependencies |

```bash
php artisan vendor:publish --provider="Morhi\StatamicInertia\ServiceProvider"
```

publishes everything at once. Each tag can also be published (or re-published with `--force`) individually. `vendor:publish` only writes missing files by default — existing files are skipped unless `--force` is passed, so re-running after an addon update is safe.

---

## Root Template

`resources/views/layout.antlers.html` (published by `statamic-inertia-views`) is set as the Inertia root view in `HandleInertiaRequests::$rootView`. It is rendered once on the first page load — subsequent Inertia navigations only return JSON, not this template.

```html
<!doctype html>
<html lang="{{ site:short_locale }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{ partial src="inertia-head" }}    <!-- @inertiaHead: SSR <head> tags, page data script -->
        {{ vite src="resources/js/app.js|resources/css/site.css" }}
    </head>
    <body>
        {{ partial src="inertia-body" }}    <!-- @inertia: the Vue app mount point -->
    </body>
</html>
```

The template is Antlers. The Blade directives (`@inertia`, `@inertiaHead`) are injected through thin Blade partials (`_inertia-head.blade.php`, `_inertia-body.blade.php`) because Statamic's Antlers engine cannot render Blade directives directly.

---

## Page Component Resolution

### Route

All frontend URLs are handled by a single wildcard route in this addon's `routes/web.php`, self-registered by `ServiceProvider` (no `bootstrap/app.php` changes needed in the host project):

```php
Route::middleware('statamic.inertia')
    ->get('/{uri?}', StatamicPageController::class)
    ->where('uri', '^(?!cp(/|$)|api(/|$)|up$|img(/|$)).*')
    ->name('inertia.page');
```

The regex excludes system paths: `cp` (Control Panel), `api` (REST API), `up` (health check), and `img` (Glide image URLs).

### Controller

`StatamicPageController` resolves the URL to a Statamic entry and maps it to a Vue component:

```php
// URL → Entry
$entry = Entry::findByUri($uri, Site::current()->handle());

// Entry → component name
// collection "pages", blueprint "page" → "Pages/Page"  → Pages/Page.vue
// collection "blog",  blueprint "article" → "Blog/Article" → Blog/Article.vue
// When collection === blueprint → just "Pages" → Pages.vue
$component = $this->resolveComponent($entry);

return Inertia::render($component, [
    'entry' => [
        'id'         => $entry->id(),
        'url'        => $entry->url(),
        'slug'       => $entry->slug(),
        'collection' => $entry->collection()->handle(),
        'blueprint'  => $entry->blueprint()->handle(),
        'data'       => $this->resolveData($entry),
    ],
]);
```

### Component naming convention

| Collection | Blueprint | Vue file |
|---|---|---|
| `pages` | `page` | `Pages/Page.vue` |
| `blog` | `article` | `Blog/Article.vue` |
| `pages` | `pages` | `Pages.vue` (deduplication) |

Components must live in `resources/js/Pages/` and are resolved via a Vite glob import:

```js
const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
return pages[`./Pages/${name}.vue`]
```

---

## Entry Data & Transformers

### EntryTransformer

`Morhi\StatamicInertia\Support\EntryTransformer` converts Statamic `Value` objects to plain PHP arrays safe for JSON serialisation. It dispatches to field-type specific transformers:

| Field type | Transformer | Output |
|---|---|---|
| `assets` | `AssetsTransformer` | `{ url, srcset, alt }` or array thereof |
| `bard` | `BardTransformer` | HTML string |
| `replicator` | `ReplicatorTransformer` | Array of set objects |
| `select` | `SelectTransformer` | Raw value(s), unwrapped from `LabeledValue` |

Transformers are registered as a singleton in `ServiceProvider::register()`:

```php
$this->app->singleton(EntryTransformer::class, fn () => new EntryTransformer([
    'assets'     => new AssetsTransformer(),
    'bard'       => new BardTransformer(),
    'replicator' => new ReplicatorTransformer(),
    'select'     => new SelectTransformer(),
]));
```

### Custom Data Providers

For blueprint- or entry-specific data shaping, register a custom provider via `EntryDataRegistry`, e.g. from your own `AppServiceProvider::boot()`:

```php
use Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry;

app(EntryDataRegistry::class)->forBlueprint('article', ArticleData::class);
app(EntryDataRegistry::class)->forEntry('my-special-slug', HomepageData::class);
```

Custom providers extend `AbstractEntryData`:

```php
use Morhi\StatamicInertia\Support\DataProviders\AbstractEntryData;

class ArticleData extends AbstractEntryData
{
    public function transform(Entry $entry): array
    {
        return $this->baseTransform($entry, ['title', 'body', 'published_at']);
    }
}
```

`baseTransform($entry, $fields)` runs the standard field transformer on the specified fields only. Omitting `$fields` transforms all entry data fields.

---

## Block Builder

Pages use a Replicator field (`content_blocks`) that supports multiple block types. The data flows from Statamic through to Vue as a typed array. The fieldsets and blueprint below are shipped as the `statamic-inertia-examples` publish group — treat them as a starting point, not fixed infrastructure.

### Fieldset structure

`resources/fieldsets/block_builder.yaml` defines a replicator with these sets:

| Set handle | Fields | Vue component |
|---|---|---|
| `text` | `content` (bard) | `Blocks/Text.vue` |
| `hero` | `title`, `subtitle`, `background_image` (asset), `cta_label`, `cta_url` | `Blocks/Hero.vue` |
| `quote` | `content` (bard), `author`, `author_image` (asset) | `Blocks/Quote.vue` |
| `card_grid` | `cards` (nested replicator with `image`, `title`, `text`, `link`) | `Blocks/CardGrid.vue` |
| `accordion` | `items` (nested replicator with `question`, `answer` bard) | `Blocks/Accordion.vue` |
| `image_caption` | `image` (asset), `caption`, `alignment` | `Blocks/ImageCaption.vue` |
| `masonry_gallery` | `column_count` (select), `images` (assets, unlimited) | `Blocks/MasonryGallery.vue` |
| `entry_listing` | `heading`, `collection` (collections, max 1), `per_page` (integer) | `Blocks/EntryListing.vue` — see [Entry Listing Block](#entry-listing-block) |

### Data flow

1. `ReplicatorTransformer` iterates over each set in `content_blocks`
2. For each set, it checks `BlockResolverRegistry` for a resolver registered against that set's handle (see below). If one is found, the resolver's output is used instead of the generic per-field transform.
3. Otherwise, for each field in the set, it retrieves the raw `Value` from the proxied Statamic collection and dispatches to the matching field transformer
4. The result is a plain PHP array passed to `entry.data.content_blocks` in the Inertia prop

### BlockResolverInterface / BlockResolverRegistry

Most blocks are a straight 1:1 mapping of fields to props, which the default per-field transform in step 3 above handles on its own. Some blocks need PHP-computed data instead — e.g. the `entry_listing` block needs to run a query rather than just pass its own fields through. `BlockResolverInterface::resolve(Values $set): array` is the escape hatch for that case; `BlockResolverRegistry::forSet($handle, $resolverClass)` maps a replicator set handle to a resolver class.

```php
namespace Morhi\StatamicInertia\Support\Blocks;

use Statamic\Fields\Values;

interface BlockResolverInterface
{
    public function resolve(Values $set): array;
}
```

`entry_listing` is registered out of the box in `ServiceProvider::bootEntryListingBlock()`:

```php
app(BlockResolverRegistry::class)->forSet('entry_listing', EntryListingBlockResolver::class);
```

A project can point a different resolver at the same handle, or register a resolver for its own custom block, from its own `AppServiceProvider::boot()` — the registry is a singleton, so a later call simply overwrites the mapping for that handle. `ReplicatorTransformer` also merges in the set's own `id` and `type` (block handle) ahead of whatever the resolver returns, so both generic and resolver-backed blocks end up with the same baseline shape on the Vue side.

### Vue rendering

`Components/Blocks.vue` (published by `statamic-inertia-scaffold`) auto-discovers all files in `Blocks/` using a Vite glob import and builds a type-to-component map at build time. The template uses a single `<component :is>` — no changes to this file are needed when adding new blocks.

```ts
const modules = import.meta.glob('../Blocks/*.vue', { eager: true })

const blockMap = Object.fromEntries(
  Object.entries(modules).map(([path, mod]) => {
    const name   = path.replace('../Blocks/', '').replace('.vue', '')
    const handle = name.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '')
    return [handle, mod.default]
  })
)
```

The filename is converted to a block type handle by PascalCase → snake_case:

| File | Handle |
|---|---|
| `Text.vue` | `text` |
| `Hero.vue` | `hero` |
| `CardGrid.vue` | `card_grid` |
| `ImageCaption.vue` | `image_caption` |
| `MasonryGallery.vue` | `masonry_gallery` |

Each matched component is rendered with `v-bind="block"`, which spreads all block fields directly as individual props. Unknown block types are silently skipped.

`defineOptions({ inheritAttrs: false })` is set on every block component to prevent undeclared block metadata (`type`, `id`, `enabled`) from leaking onto the root DOM element.

`MasonryGallery.vue`'s lightbox uses `<Teleport to="body" :disabled="!mounted">` with a `mounted` ref set in `onMounted()` — plain `renderToString()` SSR doesn't wire the Teleport anchor into `<body>`, so disabling the teleport until after client mount avoids a hydration mismatch that previously caused a blank-page crash.

### Adding a new block

1. Create a fieldset: `resources/fieldsets/block_my_block.yaml`
2. Import it in `block_builder.yaml` under a new set handle (snake_case)
3. Create `resources/js/Blocks/MyBlock.vue` — name it in PascalCase matching the handle, declare individual typed props matching the fieldset fields:

```vue
<template>
  <div class="block-my-block">
    <!-- use props directly, e.g. {{ title }} -->
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  my_field?: string
  my_image?: AssetField   // for assets fields
}>()
</script>
```

No changes to `Components/Blocks.vue` are required. Global types (`AssetField`, `Blocks`, `Block`) are declared in `resources/js/types.d.ts`.

---

## Entry Listing Block

`entry_listing` is a page-builder block (see [Block Builder](#block-builder)) that lists entries from a chosen collection with a "Load more" button, fetching further pages outside the page cache rather than through a full Inertia navigation.

### Fieldset

`resources/fieldsets/block_entry_listing.yaml`: `heading` (text, optional), `collection` (`collections` fieldtype, `max_items: 1`, required), `per_page` (integer, default `6`, `max:50`).

### EntryListingBlockResolver

Registered against the `entry_listing` set handle (see [BlockResolverInterface / BlockResolverRegistry](#block-builder)). On initial page render it:

1. Unwraps the block's `collection` field to a plain handle string — the `collections` fieldtype's augmented value is a hydrated `Collection` model, not the handle, so it reads the raw stored value the same way `Values::toArray()` does internally.
2. Calls `EntryListingQuery::paginate($collectionHandle, $perPage, 1)` for page 1.
3. Merges the query result with the block's own fields and a `load_more_url` (from `config('inertia.entry_listing.route')`):

```json
{
  "heading": "Latest posts",
  "collection": "blog",
  "per_page": 6,
  "load_more_url": "/api/entry-listing",
  "entries": [{ "id": "...", "title": "...", "url": "...", "date": "2026-01-01", "preview": {} }],
  "next_page": 2,
  "has_more": true
}
```

### EntryListingQuery

Queries published entries in the given collection for the current site, ordered by `date desc` then `id asc`. The `id` tiebreaker matters because `date` is nullable and not guaranteed unique (e.g. entries created before a collection had `dated: true`) — without it, two "Load more" page requests could return entries in a different order, causing pagination to repeat or skip entries. Each entry is mapped to `{ id, title, url, date, preview }`, where `preview` comes from `EntryListingPreviewRegistry` (see below). If `thoughtco/statamic-cache-tracker` is installed, it also dispatches `TrackContentTags` for `collection:{handle}` and `{handle}:{id}`, tagging the rendering page for cache invalidation.

### EntryListingPreviewInterface / EntryListingPreviewRegistry

Lets a project add extra fields to each listed entry's `preview` object beyond the baseline `id`/`title`/`url`/`date` — e.g. an excerpt, author, or thumbnail. Register per collection, e.g. in `AppServiceProvider::boot()`:

```php
app(\Morhi\StatamicInertia\Support\EntryListing\EntryListingPreviewRegistry::class)
    ->forCollection('blog', BlogEntryPreview::class);
```

```php
use Morhi\StatamicInertia\Support\EntryListing\EntryListingPreviewInterface;
use Statamic\Contracts\Entries\Entry;

class BlogEntryPreview implements EntryListingPreviewInterface
{
    public function preview(Entry $entry): array
    {
        return ['excerpt' => $entry->get('excerpt')];
    }
}
```

If no provider is registered for a collection, `preview` is an empty array.

### "Load more" endpoint

`EntryListingController` is mounted at `config('inertia.entry_listing.route')` (default `/api/entry-listing`, overridable via `INERTIA_ENTRY_LISTING_ROUTE`) in `routes/web.php`, deliberately outside the `statamic.inertia` middleware group and the wildcard route. It returns plain JSON (not an Inertia response), so it is never touched by `InertiaAwareStaticCache` or `InertiaJsonCache` and always serves fresh data. It validates `collection` (must be a real, existing collection handle), `page`, and `per_page` (`max:50`), then calls the same `EntryListingQuery::paginate()`.

### Cache invalidation gap it closes

Statamic's cache-tracker normally invalidates a cached page only by the specific `{collection}:{id}` tags it was rendered with. A brand-new entry — not yet tagged on any page — would never bust a listing page that should now include it. `InvalidateCollectionListingCache` (an auto-discovered listener on `EntrySaved`/`EntryDeleted`) closes this by also invalidating the broader `collection:{handle}` tag whenever any entry in that collection is saved or deleted.

### Vue: EntryListing.vue and per-collection previews

`resources/js/Blocks/EntryListing.vue` (published by `statamic-inertia-examples`) renders the initial `entries` from props, then on "Load more" click fetches the next page from `load_more_url` via `fetch()`, appending results to a local `items` ref (client-side state, not a further Inertia prop update).

Each entry is rendered through a per-collection preview component, auto-discovered the same way `Components/Blocks.vue` discovers blocks:

```ts
const previewModules = import.meta.glob('../Components/EntryPreviews/*.vue', { eager: true })
```

Filenames are mapped PascalCase → snake_case (e.g. `Blog.vue` → `blog`) to a collection handle, falling back to `EntryPreviews/Default.vue` if no collection-specific component exists. `Default.vue` (published by `statamic-inertia-examples` to `resources/js/Components/EntryPreviews/`) renders a title and date.

### Adding a preview for a collection

1. Create `resources/js/Components/EntryPreviews/{Collection}.vue`, named in PascalCase matching the collection handle (e.g. `Blog.vue` for the `blog` collection).
2. It receives the same `entry: { id, title, url, date?, preview? }` prop shape as `Default.vue` — no registration step needed on the Vue side.
3. If the preview needs extra PHP-side data beyond the baseline fields, also register an `EntryListingPreviewRegistry::forCollection()` provider as shown above.

---

## Navigation

The main navigation is managed as a Statamic Navigation structure (`content/navigation/main.yaml`, `content/trees/navigation/main.yaml`). It is built from existing entry references — nav item labels and URLs are derived directly from the referenced entries, not hardcoded.

### How it is passed to Vue

Navigation is shared as an Inertia prop **only on the initial full page load** using `Inertia::once()`. On subsequent Inertia XHR navigations the prop is omitted, preventing unnecessary re-fetching.

```php
// HandleInertiaRequests::share()
$nav = Inertia::once(function () use ($site) {
    return Nav::findByHandle('main')
        ?->in($site->handle())
        ?->flattenedPages()
        ->map(fn ($page) => [
            'label' => $page->title(),
            'href'  => $page->url(),
        ])
        ->values()
        ->all() ?? [];
});

return array_merge(['site' => ..., 'locale' => ..., 'nav' => $nav], parent::share($request));
```

### Accessing nav in Vue

```ts
// Layout.vue
const nav = usePage().props.nav as Array<{ label: string; href: string }>
```

The `<Link>` component from `@inertiajs/vue3` is used for nav items to ensure client-side navigation without full page reloads.

---

## Image Handling (Glide)

All `assets` fields are processed by `AssetsTransformer`, which generates signed Glide URLs for WebP conversion and responsive srcset support.

### Output shape

For `max_files: 1` fields (single asset), the transformer returns a single object:

```json
{
  "url":    "/img/asset/...?fm=webp&w=1920&q=85&s=...",
  "srcset": "/img/asset/...?fm=webp&w=480&q=85&s=... 480w, ...960w, ...1440w, ...1920w",
  "alt":    ""
}
```

For multi-asset fields, it returns an array of such objects.

### Usage in Vue

```html
<!-- CardGrid.vue / ImageCaption.vue -->
<img
  :src="image.url"
  :srcset="image.srcset"
  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
  :alt="image.alt"
/>

<!-- Hero.vue — background-image, srcset does not apply -->
:style="`background-image: url(${block.background_image.url})`"
```

### Glide route exclusion

The wildcard route in this addon's `routes/web.php` excludes `img` paths so that `/img/asset/...` requests bypass `StatamicPageController` and reach Statamic's built-in `GlideController`:

```php
->where('uri', '^(?!cp(/|$)|api(/|$)|up$|img(/|$)).*')
```

---

## Shared Props

`HandleInertiaRequests::share()` provides these props to every page:

| Prop | Type | Description |
|---|---|---|
| `site` | `string` | Current site handle |
| `locale` | `string` | Short locale (e.g. `en`) |
| `nav` | `Array<{label, href}>` | Main navigation (first load only, via `Inertia::once()`) |

---

## Caching

Two caching layers work in tandem.

### 1. Statamic Static Cache (HTML)

On full page loads, Statamic's static cache middleware can write fully rendered HTML to `public/static/`. `InertiaAwareStaticCache` extends it to skip this for Inertia XHR requests (identified by the `X-Inertia` header), preventing JSON payloads from being written as `.html` files.

It also protects against caching a **degraded** page: if SSR was configured to run (`config('inertia.ssr.enabled')`) but the SSR server was unreachable or errored, the response was served without pre-rendered markup. `InertiaAwareStaticCache` detects this via `SsrTrackingGateway::dispatchFailed()` (see below) and marks the response `X-Statamic-Uncacheable: true`, so Statamic's own `Cache::shouldBeCached()` skips writing it to disk. Without this, a single SSR outage could get baked into the static cache and keep serving a degraded page to everyone until the cache is next invalidated — long after SSR recovers.

### 2. Inertia JSON Cache

`InertiaJsonCache` caches Inertia's JSON responses to `public/static/json/{uri}_.json`. NGINX serves these files directly on subsequent navigation requests, bypassing PHP entirely.

Files are invalidated by:
- Content changes via `InvalidateInertiaJsonCache`, which listens to Statamic's `UrlInvalidated` event (auto-discovered from `src/Listeners/`)
- `InvalidateCollectionListingCache` (also in `src/Listeners/`), which additionally busts the `collection:{handle}` cache-tracker tag on `EntrySaved`/`EntryDeleted` — see [Entry Listing Block](#entry-listing-block) for why this is needed on top of the tag-based invalidation above
- Running `php artisan statamic:static:clear` or `cache-tracker:flush`, which deletes the entire `public/static/json/` directory (registered as a `CommandFinished` listener in `ServiceProvider::bootAddon()`)

```
# Manually clear the Inertia JSON cache
ddev php artisan statamic:static:clear
```

### Cache-Control for Inertia responses

`HandleInertiaRequests::handle()` adds `Cache-Control: no-store` to all Inertia JSON responses, preventing browsers from serving a stale JSON payload when the user navigates using the back button.

### X-Cache-Status header

Every response carries an `X-Cache-Status` header for debugging which cache layer (if any) served it:

| Value | Set by | Meaning |
|---|---|---|
| `HIT` | NGINX (`.ddev/nginx_full/nginx-site.conf`) | Served directly from a static file on disk (`public/static/` or `public/static/json/`) — PHP never ran |
| `MISS` | `InertiaAwareStaticCache` | Request reached PHP; a normal, cacheable `GET` response |
| `BYPASS` | `InertiaAwareStaticCache` | Request reached PHP but is inherently uncacheable — non-`GET`, or a live preview request |

NGINX can only set the header on the path where it serves a cached file straight from disk: any `try_files ... /index.php` fallback re-enters the server block's rewrite phase from scratch, so an nginx variable or `add_header` set before that fallback does not survive into the eventual PHP-handled response. That's why `HIT` is set exclusively by NGINX, while `MISS`/`BYPASS` are set by the Laravel middleware instead — the two never overlap for a single response.

---

## Statamic Live Preview

`utils/usePreviewRefresh.ts` enables the Statamic CP live preview to update the frontend while editing. It listens for `postMessage` events from the CP iframe and triggers an Inertia partial reload with the preview token:

```ts
function onPreviewMessage(e: MessageEvent) {
    if (e.data?.name !== 'statamic.preview.updated') return
    router.reload({ headers: { 'X-Statamic-Token': e.data.token } })
}
```

Call it in any layout or page component that should update on CP edits:

```ts
// Layout.vue
usePreviewRefresh()
```

---

## Development Setup

### Prerequisites

- DDEV running
- Node.js (version specified in `.nvmrc`)

### Start the dev server

```bash
ddev vite
```

Vite runs on `vite.statamic.ddev.site` (proxied by DDEV). The `cors: true` option in `vite.config.js` allows the main domain (`statamic.ddev.site`) to load HMR assets from the Vite subdomain.

The `VITE_SERVER_URI` environment variable controls the asset origin URL injected into the HTML. It is set in `.env` and used by the DDEV Vite sidecar.

### Build for production

```bash
ddev npm run build
```

This produces both the client bundle (`public/build/`) and the SSR bundle (`bootstrap/ssr/ssr.js`). Always rebuild after changing any Vue component or JS file to keep the SSR bundle in sync with the client bundle — stale SSR bundles cause hydration mismatches.

---

## SSR Setup

Server-side rendering is handled by a Node.js process that runs the SSR bundle built by Vite.

### How it works

1. Vite builds `resources/js/ssr.js` → `bootstrap/ssr/ssr.js`
2. On an incoming full page load, Inertia calls the SSR server (default `http://127.0.0.1:13714`) to pre-render the Vue component to an HTML string
3. The HTML is injected into the `@inertia` div in `layout.antlers.html`
4. The client receives the pre-rendered HTML and hydrates it using `createSSRApp()`

### Start the SSR server

```bash
ddev php artisan inertia:start-ssr
```

This starts the Node.js SSR server using the built `bootstrap/ssr/ssr.js` bundle. The server must be running for SSR to work; without it, Inertia falls back to client-side rendering.

In production, manage this process with a supervisor or pm2 to keep it alive.

### SSR failure tracking

`SsrTrackingGateway` (`ServiceProvider::bootSsrTracking()`) wraps Inertia's own `Gateway` binding to record, per request, whether SSR was attempted (`config('inertia.ssr.enabled')`) and whether it actually returned a response. This is needed because the `@inertia`/`@inertiaHead` Blade directives call `Gateway::dispatch()` themselves and only expose the result to a local view variable — nothing else in the request lifecycle can otherwise observe whether SSR rendered successfully. `InertiaAwareStaticCache` reads `dispatchFailed()` off this same singleton afterward to avoid statically caching a page that fell back to client-side rendering — see [Caching](#caching).

### SSR configuration

`config/inertia.php` (published by `statamic-inertia-config`, or read directly from the addon if unpublished) controls SSR behaviour:

```php
'ssr' => [
    'enabled' => true,
    'url'     => 'http://127.0.0.1:13714',
],
```

### Important: keep the SSR bundle in sync

The SSR bundle at `bootstrap/ssr/ssr.js` is a **compiled artefact**. If it gets out of date with the source components, Vue will report hydration mismatches in the browser console (text mismatches, missing nodes, etc.). Always run `ddev npm run build` after making frontend changes before starting or restarting the SSR server.

---

## Adding a New Page Type

To add a new collection/blueprint that renders with a dedicated Vue component:

1. Create the blueprint in `resources/blueprints/collections/{collection}/{blueprint}.yaml`
2. Create the Vue component at `resources/js/Pages/{Collection}/{Blueprint}.vue`

```vue
<script setup lang="ts">
import Layout from '../Layout.vue'
defineOptions({ layout: Layout })

const props = defineProps<{
  entry: {
    id: string
    url: string
    slug: string
    collection: string
    blueprint: string
    data: Record<string, unknown>
  }
}>()
</script>

<template>
  <main>
    <!-- use props.entry.data -->
  </main>
</template>
```

If you need custom data shaping beyond the default field transformers, register a data provider (e.g. in your own `AppServiceProvider::boot()`):

```php
app(\Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry::class)
    ->forBlueprint('article', ArticleData::class);
```

---

## Adding a New Block

1. Create the fieldset at `resources/fieldsets/block_{handle}.yaml`
2. Import it in `block_builder.yaml`:

```yaml
my_block:
  display: 'My Block'
  fields:
    - import: block_my_block
```

3. Create the Vue component at `resources/js/Blocks/MyBlock.vue`:

```vue
<template>
  <div class="block-my-block">
    <!-- block.my_field -->
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  my_field?: string
}>()
</script>
```

No registration step is required — `Components/Blocks.vue` auto-discovers new files in `Blocks/` via a Vite glob import and maps them by PascalCase → snake_case handle.

If the block contains `assets` fields, they are automatically transformed to `{ url, srcset, alt }` by `AssetsTransformer`. For `bard` fields you get an HTML string. No additional configuration is needed.
