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
| `statamic-inertia-config` | `config/statamic-inertia.php` | Entry listing and globals whitelist configuration |
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

## Documentation

See [INERTIA.md](INERTIA.md) for the full architecture: request lifecycle, page component resolution, entry data transformers, the block builder, navigation, image handling, caching, live preview, SSR setup, and guides for adding new page types and blocks.

## License

This project is licensed under the [Business Source License 1.1](LICENSE.md). Free for personal and non-commercial use; commercial use requires a separate license from the author. On 2099-12-31 it converts to MIT.
