# Roadmap

Ideas for future work on this addon. Nothing here is committed or scheduled — this is a collection point, not a spec. Entries should be turned into real documentation (INERTIA.md) once implemented.

---

## Taxonomies

Currently only entries have a transform/render path (`StatamicPageController`, `EntryTransformer`). Taxonomy terms have no equivalent:

- A `TermTransformer` (or reuse of `EntryTransformer` against term fields) so blueprint fields on terms go through the same field-type transformers (assets, bard, replicator, select).
- A term listing endpoint/prop, similar in spirit to `EntryListingController` / `EntryListingQuery`, so a taxonomy index page (e.g. `/blog/category/design`) can list entries tagged with a term plus the term's own fields (description, image, ...).
- Decide whether taxonomy term pages get their own Vue component resolution convention (e.g. `Taxonomies/{Taxonomy}/{Blueprint}.vue`) analogous to the existing collection → `Pages/{Collection}/{Blueprint}.vue` mapping.

## Forms

No support yet for Statamic Forms (native forms, not just Bard/asset fields). Likely shape:

- A generic form submission endpoint/controller that accepts an Inertia form post and forwards it to Statamic's `Form` facade, returning validation errors in Inertia's expected shape (`errors` prop) so `useForm()` in Vue works out of the box.
- A blueprint-driven form renderer, so a `form` fieldtype/reference field on an entry can resolve its handle to a form's field definitions and pass them as props to a generic `Form.vue`, similar to how blocks resolve their fieldset shape.
- Consider CSRF/honeypot/recaptcha compatibility with Inertia's XHR-based navigation (no full page reload on submit).

## GlideImage component

`AssetsTransformer` already emits `{ url, srcset, alt }` with a fixed set of breakpoints (see INERTIA.md "Image Handling (Glide)"). Every consumer currently has to hand-write `<img :src :srcset sizes>` manually (see `CardGrid.vue`, `ImageCaption.vue`).

Idea: ship a `GlideImage.vue` component (published via `statamic-inertia-scaffold` or `statamic-inertia-examples`) that:

- Accepts the `AssetField` shape directly as a prop and renders the `<img>` tag, so call sites become `<GlideImage :image="block.image" sizes="..." />`.
- Optionally supports `background` mode (for use cases like `Hero.vue`'s `background-image` inline style) so that path doesn't need to bypass the component.
- Consider whether breakpoints/quality should become configurable per-field (currently hardcoded in `AssetsTransformer`) before baking too much into the component API.

## Project data transformer examples

`INERTIA.md` documents the `EntryDataRegistry` / `AbstractEntryData` mechanism for per-blueprint or per-entry data shaping, but there's no worked example showing a realistic transformer beyond the one-liner in the docs. Worth adding (likely to `stubs/` as an opt-in example, or as a docs-only walkthrough):

- A full example `ArticleData` provider showing `baseTransform()` combined with custom logic — e.g. computing a reading time, resolving a related-entries list, or reshaping a nested field into a different JSON structure than the raw fieldtype output.
- An example of a fieldset-level transformer for a *field*, not just an *entry* — i.e. how to register a custom `FieldTransformerInterface` implementation for a new fieldtype (the docs show the built-in four, but not the steps to add a fifth as a project-level extension point, since `EntryTransformer`'s transformer map is currently only set from within this addon's `ServiceProvider::register()`).

## 404 handling / error pages

`StatamicPageController::__invoke()` currently does `abort_unless($entry, 404)`, which falls back to Laravel's default (non-Inertia) HTML error page — it never goes through Inertia rendering, so there's no styled 404/500 within the Vue app. Laravel's own Inertia adapter supports mapping error responses to an Inertia page (see the `handleRequest`/exception handler pattern in `inertiajs/inertia-laravel`). Worth adding a documented pattern for a `Pages/Error.vue` (or reusing `Pages/Page.vue` with a status prop) so error states get the same layout/nav as the rest of the site instead of dropping out of the SPA shell.

## Redirects

Statamic ships a first-party Redirect fieldtype/collection concept in some setups (or a custom `redirects` collection is common). Nothing in `StatamicPageController` currently checks for a configured redirect before calling `abort_unless($entry, 404)` — a URI that used to resolve to an entry and was since redirected just 404s. Worth deciding whether redirect lookup belongs in this addon (e.g. checking a `redirects` collection/global before the 404) or is left entirely to the host project/nginx layer.

## Multi-site support

`StatamicPageController` and `EntryListingQuery` both call `Site::current()->handle()`, and `HandleInertiaRequests::share()` exposes a single `site`/`locale` pair, but nothing in the addon or its docs addresses how site switching (e.g. a language switcher) is supposed to work — resolving the equivalent entry on another site, building its URL, and updating the shared props without a full reload. Worth a documented pattern (or an explicit "single-site only for now" caveat in INERTIA.md if multi-site isn't actually a target).

## Search

No search endpoint exists alongside `EntryListingController`. Statamic ships a `Search` facade/index; a `SearchController` returning Inertia JSON (or a lightweight JSON API endpoint outside the Inertia page flow, similar to how `img` is excluded from the catch-all route) would let a Vue search box query without a full page navigation.

## SSR failure visibility

`SsrTrackingGateway::dispatchFailed()` already tracks whether SSR was attempted but came back empty (see its docblock) but nothing currently reads that value outside `InertiaAwareStaticCache` (worth double-checking whether it's consumed anywhere else, e.g. logging/alerting) — an SSR outage in production would currently degrade silently to client-side rendering with no visibility. Worth wiring it to a log warning or a health-check-facing metric so an SSR process crash gets noticed.

## Test coverage

`tests/` currently only has the Testbench `TestCase.php` scaffold and a placeholder `ExampleTest.php` — no feature tests exist yet for `StatamicPageController` (component resolution, 404s), `EntryTransformer`/field transformers, `EntryListingQuery` pagination/ordering, or the JSON cache invalidation listeners. Worth prioritizing before adding more surface area (Taxonomies/Globals/Forms above), since each of those will be harder to change safely without a baseline.

## Better Vue component typings for Inertia data

Every page component currently types its props as `entry: { ..., data: Record<string, unknown> }` (see `Pages/Page.vue`, `Pages/Blog.vue`, and the "Adding a New Page Type" example in INERTIA.md), and block components hand-declare their own props with no link back to the fieldset that produced them (`types.d.ts`'s `Block extends Record<string, any>` is deliberately loose). This means a typo in a block's prop name, or a field renamed in a blueprint, silently produces `undefined` in Vue with no compile-time signal.

Ideas, roughly in order of effort:

- At minimum, per-blueprint TS interfaces that a project hand-maintains next to each blueprint (e.g. `types/blueprints/article.d.ts` exporting an `ArticleData` interface), with the page component prop typed as `entry: Entry<ArticleData>` using a shared generic `Entry<T>` helper type (replacing the current inline shape repeated in every page component and in INERTIA.md's example).
- A generic per-block-type prop interface for each entry in `stubs/js-examples/*.vue`, replacing today's loose `Block`/`Record<string, any>` — e.g. exported `HeroBlock`, `CardGridBlock` interfaces in `types.d.ts` that each block component's `defineProps<T>()` references, so `Components/Blocks.vue`'s dynamic dispatch has *something* checkable on the authoring side even though the dispatch itself stays untyped (`v-bind="block"` can't be statically checked either way).
- A longer-term, higher-effort option: generate TS types from the blueprint YAML itself (field handle → fieldtype → TS type mapping, reusing the same fieldtype-to-transformer-output mapping documented in INERTIA.md's "Entry Data & Transformers" table) via an `artisan` command, so blueprint changes and their TS types can't drift apart. Would need a decision on where generated files live (`resources/js/types/generated/`?) and whether it runs automatically (a build step / file watcher) or on demand.
- Whatever shape this takes, it should cover the four prop shapes that currently exist ad hoc: entry page props (`entry.data`), block props (individual block fields spread via `v-bind`), `EntryListingPreviewInterface`'s per-collection `preview` object (currently just `Record<string, any>` in `EntryListing.vue`/`Default.vue`), and the `globals` shared prop (see [Globals](#globals)) — today typed ad hoc per-component, e.g. `Layout.vue`'s `{ general?: { site_name: string } }` and `Footer.vue`'s hand-written `FooterGlobals`/`SocialLink` interfaces, with no link back to `config('inertia.globals')` or the underlying global set blueprints (`resources/blueprints/globals/*.yaml`). A rename or whitelist change currently fails silently the same way an entry field rename does.
- For globals specifically, the codegen source is two-layered: the blueprint (field handle → fieldtype, same as entries) *and* `config('inertia.globals')` (which vars are actually whitelisted, and any `GlobalValueTransformer` reshaping — which the generator can't see through, so a transformed var would need a manual override or an `@return` type hint convention on the transformer class). A generated `GlobalsProps` interface (`{ general?: {...}, footer?: {...} }`) would need to intersect "fields present in the blueprint" with "vars whitelisted in config" — a global set with 10 fields but 3 whitelisted vars should only generate types for the 3.
- Worth exploring the `artisan` command as something callable two ways: directly (`artisan inertia:generate-types`, deterministic, no LLM involved — plain YAML/config parsing same as any other codegen) and via an AI coding agent (a documented prompt/skill that shells out to the same command, or that reads blueprints itself when the deterministic command can't infer a shape — e.g. resolving what a `GlobalValueTransformer` returns, which is arbitrary PHP). The deterministic path should stay the default; the agent path is a fallback for the parts static analysis can't cover, not a replacement.

## Open questions to revisit before starting any of the above

- Should Taxonomies/Forms live in this addon at all, or should they be split into separate optional addons/packages, given `morhi/statamic-inertia` is meant to stay a thin bridge layer? (Per [[feedback_addon_vs_app_code]], new reusable feature logic belongs in the addon, but that doesn't settle whether it belongs in *this* addon vs. a sibling one.)
- For anything that adds a new shared prop (Nav-like data), check current payload size impact — Inertia ships shared props on every request, so this should stay opt-in/lazy where the data isn't needed on all pages. (Globals now follows this: whitelist-based, only whitelisted vars are computed/shared.)
