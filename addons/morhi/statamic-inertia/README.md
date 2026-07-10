# Statamic Inertia

Bridges Statamic's PHP backend with an Inertia.js + Vue 3 frontend, including SSR support. Statamic serves as the data layer; Vue handles all rendering.

## Installation

```bash
composer require morhi/statamic-inertia
php artisan vendor:publish --provider="Morhi\StatamicInertia\ServiceProvider"
npm install
npm run build
```

This publishes:

| Tag | Destination | Contents |
|---|---|---|
| `statamic-inertia-config` | `config/inertia.php` | `inertiajs/inertia-laravel` configuration |
| `statamic-inertia-scaffold` | `resources/js/` | Core Inertia/Vue bootstrap: `app.js`, `ssr.js`, `types.d.ts`, `utils/`, `Pages/Layout.vue`, `Pages/Pages/Page.vue`, `Components/Blocks.vue` |
| `statamic-inertia-examples` | `resources/js/Blocks/`, `resources/fieldsets/`, `resources/blueprints/collections/pages/` | Optional starter blocks (Text, Hero, Quote, CardGrid, Accordion, ImageCaption, MasonryGallery) with matching fieldsets and a `page` blueprint — safe to delete or replace |
| `statamic-inertia-views` | `resources/views/` | Root Antlers template (`layout.antlers.html`), a body-only variant (`inertia.antlers.html`), and Blade partials for `@inertia`/`@inertiaHead` |
| `statamic-inertia-project-files` | `vite.config.js`, `package.json` | Vite config (Vue + Tailwind + SSR entry) and npm dependencies |

Publish only what you need with `--tag=`, e.g. to skip the example blocks:

```bash
php artisan vendor:publish --tag=statamic-inertia-config
php artisan vendor:publish --tag=statamic-inertia-scaffold
php artisan vendor:publish --tag=statamic-inertia-views
php artisan vendor:publish --tag=statamic-inertia-project-files
```

Re-run any tag with `--force` to overwrite existing files after an addon update.

## Architecture

```
Browser request
    │
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

### Page component resolution

Every public URL is handled by a single wildcard route (`routes/web.php` in this addon), registered with the `statamic.inertia` middleware group. It resolves the URL to a Statamic entry and maps it to a Vue component using the convention `{StudlyCollection}/{StudlyBlueprint}`:

| Collection | Blueprint | Vue file |
|---|---|---|
| `pages` | `page` | `Pages/Page.vue` |
| `blog` | `article` | `Blog/Article.vue` |
| `pages` | `pages` | `Pages.vue` (deduplicated) |

### Entry data & transformers

`Support\EntryTransformer` converts Statamic field values into JSON-safe data, dispatching per fieldtype to a `FieldTransformerInterface` implementation (`assets`, `bard`, `replicator`, `select` are covered out of the box). For blueprint- or entry-specific data shaping, register a custom provider:

```php
app(\Morhi\StatamicInertia\Support\DataProviders\EntryDataRegistry::class)
    ->forBlueprint('article', ArticleData::class);
```

where `ArticleData extends \Morhi\StatamicInertia\Support\DataProviders\AbstractEntryData`.

### Caching

Two layers work together:

- **Statamic static cache**: `InertiaAwareStaticCache` skips Statamic's HTML static cache on Inertia XHR requests (identified by the `X-Inertia` header), so JSON payloads never get written as `.html` files.
- **Inertia JSON cache**: `InertiaJsonCache` writes Inertia JSON responses to `public/static/json/{uri}_.json` when `statamic.static_caching.strategy` is `full`. The `InvalidateInertiaJsonCache` listener deletes the matching file on Statamic's `UrlInvalidated` event; a `CommandFinished` listener clears the whole directory on `statamic:static:clear` / `cache-tracker:flush`.

### SSR

`resources/js/ssr.js` (published by `statamic-inertia-scaffold`) is built by `vite build --ssr` into `bootstrap/ssr/ssr.js`. Start the SSR server with:

```bash
php artisan inertia:start-ssr
```

Rebuild (`npm run build`) after every frontend change to keep the SSR bundle in sync — a stale bundle causes hydration mismatches.

### Adding a new page type

1. Create a blueprint at `resources/blueprints/collections/{collection}/{blueprint}.yaml`.
2. Create `resources/js/Pages/{Collection}/{Blueprint}.vue` using `Pages/Pages/Page.vue` as a reference (import `Layout` from `Pages/Layout.vue`, `defineOptions({ layout: Layout })`).

### Adding a new block

1. Create a fieldset at `resources/fieldsets/block_{handle}.yaml` and import it into `block_builder.yaml`.
2. Create `resources/js/Blocks/MyBlock.vue`, named in PascalCase matching the snake_case block handle. `Components/Blocks.vue` auto-discovers it via a Vite glob import — no wiring needed.

`assets` fields are transformed to `{ url, srcset, alt }` (Glide-backed, WebP, responsive `srcset`); `bard` fields become an HTML string.
