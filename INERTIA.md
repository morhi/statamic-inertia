# Inertia.js Integration

This project uses [Inertia.js](https://inertiajs.com/) to bridge Statamic's PHP backend with a Vue 3 + TypeScript frontend, including full SSR support. Statamic serves as the data layer; Vue handles all rendering.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Request Lifecycle](#request-lifecycle)
3. [File Structure](#file-structure)
4. [Root Template](#root-template)
5. [Page Component Resolution](#page-component-resolution)
6. [Entry Data & Transformers](#entry-data--transformers)
7. [Block Builder](#block-builder)
8. [Navigation](#navigation)
9. [Image Handling (Glide)](#image-handling-glide)
10. [Shared Props](#shared-props)
11. [Caching](#caching)
12. [Statamic Live Preview](#statamic-live-preview)
13. [Development Setup](#development-setup)
14. [SSR Setup](#ssr-setup)
15. [Adding a New Page Type](#adding-a-new-page-type)
16. [Adding a New Block](#adding-a-new-block)

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

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── InertiaController.php          # Abstract base: view(), resolveComponent()
│   │   └── StatamicPageController.php     # Resolves any URL to an Inertia page
│   └── Middleware/
│       ├── HandleInertiaRequests.php      # Shared props, nav, no-store cache header
│       ├── InertiaAwareStaticCache.php    # Prevents Statamic static cache on XHR
│       └── InertiaJsonCache.php           # Caches Inertia JSON responses to disk
├── Listeners/
│   └── InvalidateInertiaJsonCache.php    # Deletes stale JSON cache on content change
├── Providers/
│   └── AppServiceProvider.php             # Registers transformers, middleware group, listeners
└── Support/
    ├── EntryTransformer.php               # Transforms Statamic entry fields to JS-safe values
    ├── DataProviders/
    │   ├── EntryDataInterface.php         # Contract for custom data providers
    │   ├── AbstractEntryData.php          # Base class with transformer access
    │   └── EntryDataRegistry.php          # Maps blueprint/entry → custom data provider
    └── Transformers/
        ├── FieldTransformerInterface.php  # Contract: transform(Value): mixed
        ├── AssetsTransformer.php          # assets fields → { url, srcset, alt }
        ├── BardTransformer.php            # bard fields → HTML string
        └── ReplicatorTransformer.php      # replicator fields → array of sets

resources/
├── js/
│   ├── app.js                            # Client-side Inertia bootstrap (SSR hydration)
│   ├── ssr.js                            # Server-side Inertia bootstrap
│   ├── types.d.ts                        # Block type definitions
│   ├── utils/
│   │   └── usePreviewRefresh.ts          # Statamic live preview support
│   ├── Pages/
│   │   ├── Layout.vue                    # Persistent layout (header, nav)
│   │   └── Pages/
│   │       └── Page.vue                  # Default page component
│   ├── Components/
│   │   └── Blocks.vue                    # Renders block array by type
│   └── Blocks/
│       ├── Text.vue
│       ├── Hero.vue
│       ├── Quote.vue
│       ├── CardGrid.vue
│       ├── Accordion.vue
│       └── ImageCaption.vue
└── views/
    ├── layout.antlers.html               # Root HTML template
    ├── inertia.antlers.html              # Minimal view (body only)
    └── partials/
        ├── _inertia-head.blade.php       # @inertiaHead directive
        └── _inertia-body.blade.php       # @inertia directive

routes/
├── web.php                               # Standard Statamic routes
└── inertia.php                           # Catch-all wildcard → StatamicPageController

config/
└── inertia.php                           # Inertia config (SSR host/port, component paths)
```

---

## Root Template

`resources/views/layout.antlers.html` is set as the Inertia root view in `HandleInertiaRequests::$rootView`. It is rendered once on the first page load — subsequent Inertia navigations only return JSON, not this template.

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

All frontend URLs are handled by a single wildcard route in `routes/inertia.php`:

```php
Route::get('/{uri?}', StatamicPageController::class)
    ->where('uri', '^(?!cp(/|$)|api(/|$)|up$|img(/|$)).*')
    ->name('inertia.page');
```

The regex excludes system paths: `cp` (Control Panel), `api`, `up` (health check), and `img` (Glide image URLs).

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

`app/Support/EntryTransformer.php` converts Statamic `Value` objects to plain PHP arrays safe for JSON serialisation. It dispatches to field-type specific transformers:

| Field type | Transformer | Output |
|---|---|---|
| `assets` | `AssetsTransformer` | `{ url, srcset, alt }` or array thereof |
| `bard` | `BardTransformer` | HTML string |
| `replicator` | `ReplicatorTransformer` | Array of set objects |

Transformers are registered as a singleton in `AppServiceProvider`:

```php
$this->app->singleton(EntryTransformer::class, fn () => new EntryTransformer([
    'assets'     => new AssetsTransformer(),
    'bard'       => new BardTransformer(),
    'replicator' => new ReplicatorTransformer(),
]));
```

### Custom Data Providers

For blueprint- or entry-specific data shaping, register a custom provider via `EntryDataRegistry`:

```php
// In AppServiceProvider::boot() or a separate provider
app(EntryDataRegistry::class)->forBlueprint('article', ArticleData::class);
app(EntryDataRegistry::class)->forEntry('my-special-slug', HomepageData::class);
```

Custom providers extend `AbstractEntryData`:

```php
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

Pages use a Replicator field (`content_blocks`) that supports multiple block types. The data flows from Statamic through to Vue as a typed array.

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

### Data flow

1. `ReplicatorTransformer` iterates over each set in `content_blocks`
2. For each field in a set, it retrieves the raw `Value` from the proxied Statamic collection and dispatches to the matching transformer
3. The result is a plain PHP array passed to `entry.data.content_blocks` in the Inertia prop

### Vue rendering

`Components/Blocks.vue` auto-discovers all files in `Blocks/` using a Vite glob import and builds a type-to-component map at build time. The template uses a single `<component :is>` — no changes to this file are needed when adding new blocks.

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

Each matched component is rendered with `v-bind="block"`, which spreads all block fields directly as individual props. Unknown block types are silently skipped.

`defineOptions({ inheritAttrs: false })` is set on every block component to prevent undeclared block metadata (`type`, `id`, `enabled`) from leaking onto the root DOM element.

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

The NGINX wildcard catch-all in `routes/inertia.php` excludes `img` paths so that `/img/asset/...` requests bypass `StatamicPageController` and reach Statamic's built-in `GlideController`:

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

### 2. Inertia JSON Cache

`InertiaJsonCache` caches Inertia's JSON responses to `public/static/json/{uri}_.json`. NGINX serves these files directly on subsequent navigation requests, bypassing PHP entirely.

Files are invalidated by:
- Content changes via `InvalidateInertiaJsonCache`, which listens to Statamic's `UrlInvalidated` event
- Running `php artisan statamic:static:clear` or `cache-tracker:flush`, which deletes the entire `public/static/json/` directory

```
# Manually clear the Inertia JSON cache
ddev php artisan statamic:static:clear
```

### Cache-Control for Inertia responses

`HandleInertiaRequests::handle()` adds `Cache-Control: no-store` to all Inertia JSON responses, preventing browsers from serving a stale JSON payload when the user navigates using the back button.

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

### SSR configuration

`config/inertia.php` controls SSR behaviour:

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

If you need custom data shaping beyond the default field transformers, register a data provider in `AppServiceProvider::boot()`:

```php
app(EntryDataRegistry::class)->forBlueprint('article', ArticleData::class);
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
import { PropType } from 'vue'
defineProps({ block: Object as PropType<Block> })
</script>
```

4. Register it in `Components/Blocks.vue`:

```vue
import MyBlock from '../Blocks/MyBlock.vue'

<MyBlock v-if="block.type === 'my_block'" :block="block" />
```

If the block contains `assets` fields, they are automatically transformed to `{ url, srcset, alt }` by `AssetsTransformer`. For `bard` fields you get an HTML string. No additional configuration is needed.
