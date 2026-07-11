---
id: 644a4250-cb39-4b53-9476-d04677864f97
blueprint: blog
title: "What's Next: A Sneak Peek at Upcoming Features"
content_blocks:
  -
    id: upcoming-hero-001
    type: hero
    enabled: true
    title: "What's Next: A Sneak Peek at Upcoming Features"
    subtitle: "A deep dive into what is cooking on the roadmap for this site's Vue and Statamic foundation."
    background_image: tsuyoshi-kozu-boc-f7jwdek-unsplash.jpg
  -
    id: upcoming-text-001
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: "This site keeps Statamic as the source of truth while Vue renders every page through Inertia. Every entry, every page-builder block, and this very blog post you are reading right now flows from Statamic's flat-file content straight into a Vue 3 component, without a separate REST or GraphQL layer standing in between. It is a deliberately thin bridge: Statamic owns the content and the editing experience, Inertia moves the data across, and Vue owns everything a visitor actually sees."
      -
        type: paragraph
        content:
          -
            type: text
            text: 'That foundation has proven itself well enough that we can now be honest about what is still missing. Right now the addon knows how to render entries and their page-builder blocks, and it exposes a small, deliberately public entry-listing API for paginated blocks like the one at the bottom of this post. Everything else, taxonomies, global content, native forms, and search, is still on the drawing board. This post is a tour of that drawing board.'
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'None of what follows is scheduled or guaranteed to ship in this exact shape. It is closer to a lab notebook than a changelog.'
  -
    id: upcoming-image-001
    type: image_caption
    enabled: true
    image: marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
    caption: 'The roadmap lives in a single markdown file, not a project board. Ideas graduate to real documentation once they are actually built.'
    alignment: center
  -
    id: upcoming-text-taxonomies
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'Taxonomy pages'
      -
        type: paragraph
        content:
          -
            type: text
            text: "Right now, only entries have a rendering path. A blog post like this one gets resolved by StatamicPageController, transformed field by field through EntryTransformer, and mapped to a Vue component based on its collection and blueprint. Taxonomy terms, the tags and categories that group entries together, have no equivalent yet. If this post were tagged 'roadmap' today, there would be nowhere on the live site for a visitor to click through and see every other post tagged the same way."
      -
        type: paragraph
        content:
          -
            type: text
            text: "The plan is to give terms the same treatment entries already get. A TermTransformer, or a reuse of the existing EntryTransformer machinery against a term's own blueprint fields, would let taxonomy terms carry assets, bard content, and replicator blocks through the same field-type transformers that already power entries, so a term's description field, its header image, or a custom block on the term itself all arrive in Vue in the same predictable shape editors already expect from entries."
      -
        type: paragraph
        content:
          -
            type: text
            text: "Alongside that, a term listing endpoint in the same spirit as the entry-listing API this post's Load more button uses would let a taxonomy index page, something like /blog/category/design, list every entry tagged with that term, paginated the same way. The open question still on the table is whether taxonomy pages get their own Vue component resolution convention, mirroring how collections resolve to Pages/{Collection}/{Blueprint}.vue today, or whether they piggyback on the existing page components with an extra prop instead."
  -
    id: upcoming-text-globals
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'Global sets'
      -
        type: paragraph
        content:
          -
            type: text
            text: "Statamic's Global Sets are the natural home for the kind of content that lives on every page rather than one single entry: site-wide settings, footer content, social links, a phone number in the header. Nothing in this addon currently exposes them to Vue at all, so today that kind of content either does not exist or has to be hand-copied into every entry that needs it."
      -
        type: paragraph
        content:
          -
            type: text
            text: 'The likely shape is a shared Inertia prop, exposed the same way the existing nav prop is shared today via Inertia::once() inside HandleInertiaRequests::share(), computed once per request and available to every page component without each one having to ask for it explicitly.'
      -
        type: paragraph
        content:
          -
            type: text
            text: "The part that needs more thought is scope, and not every global belongs on every page. The current thinking is to allow scoping at three levels: per site, so a region-specific global only reaches that site's pages; per collection, so a global meant for a news section only reaches pages in the news collection rather than every page and blog post; and per blueprint, narrower still, for a global that should only reach entries built from one specific blueprint within a collection."
      -
        type: paragraph
        content:
          -
            type: text
            text: "Exposure itself should be whitelist-based rather than automatic. No global set, or field within one, should reach Vue unless it is explicitly listed in config alongside the site, collection, or blueprint scope it belongs to. That avoids the failure mode where a global added for one narrow use case accidentally leaks into every other page's props simply because it exists somewhere in Statamic. And whichever fields end up inside those globals, an asset, a bard field, a replicator block, should go through the same EntryTransformer field transformers entries already use, rather than being passed through raw and forcing every consuming Vue component to reinvent that logic on its own."
      -
        type: paragraph
        content:
          -
            type: text
            text: 'A whitelist entry does not have to spell out every field one by one. Using a wildcard to mean every var in that global set is still a deliberate, explicit choice made by whoever wrote the config, not an accidental default someone forgot to lock down, so it stays perfectly in keeping with the whitelist model. What the design actually guards against is exposure nobody asked for, not exposure that happens to be broad. A global set writing '
          -
            type: text
            marks:
              -
                type: bold
            text: '*'
          -
            type: text
            text: ' into its whitelist entry is making exactly as intentional a decision as one that lists three field handles by name.'
  -
    id: upcoming-text-forms
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'Native forms'
      -
        type: paragraph
        content:
          -
            type: text
            text: "There is no support yet for Statamic's own Forms feature. The addon can already render a Bard field or an asset upload inside an entry, but a genuine, submittable contact-form-style form backed by Statamic's Form facade is a different, more interactive beast entirely."
      -
        type: paragraph
        content:
          -
            type: text
            text: "The plan centers on two pieces. First, a generic form submission endpoint that accepts an Inertia form post, forwards it to Statamic's Form facade for validation and storage exactly the way a native Statamic form does today, and returns validation errors in the shape Inertia's own useForm() composable already expects, so a Vue component built around useForm() just works, with no bespoke error-handling glue required on the frontend. Second, a blueprint-driven form renderer: a fieldtype or reference field on an entry that points at a form handle, resolves that form's own field definitions, and hands them to a generic Form.vue component as props, the same pattern this addon already uses for page-builder blocks, where a block's fieldset shape becomes the block component's props."
      -
        type: paragraph
        content:
          -
            type: text
            text: "The trickier part is everything Inertia changes about how a page normally behaves. A classic Statamic form assumes a full page reload on submit, with honeypot and CSRF protection built around that assumption. Inertia's whole premise is that navigation happens over XHR without a full reload, so the plumbing for CSRF tokens, honeypot fields, and optionally reCAPTCHA all needs to be re-checked against that model before this can be more than a demo."
  -
    id: upcoming-text-search
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'Site search'
      -
        type: paragraph
        content:
          -
            type: text
            text: 'There is currently no way to search this site without leaving the Vue app entirely. Statamic ships a Search facade and index out of the box, used by plenty of Antlers-rendered sites, but nothing in this addon exposes it to a Vue frontend yet.'
      -
        type: paragraph
        content:
          -
            type: text
            text: "The plan is a SearchController that behaves a lot like the entry-listing API powering the Load more button on this very post: a plain JSON endpoint, deliberately outside the main Inertia page flow so it is never touched by the static or JSON caching layers, that a Vue search box can call directly. Type a query, get JSON results back, and update the results list without a full page navigation, similar in spirit to how the img route is already excluded from the catch-all wildcard route so Glide image requests never get routed through Statamic's entry resolver."
      -
        type: paragraph
        content:
          -
            type: text
            text: "Exactly which fields get indexed and how results get ranked is still open. Statamic's Search facade already supports indexing arbitrary field combinations per collection, so the more interesting design question is on the Vue side: how much of the results payload should mirror the shape entries already have when transformed by EntryTransformer, so a search result card can reuse the same preview components this site's collection listing block already uses."
  -
    id: upcoming-cards-001
    type: card_grid
    enabled: true
    cards:
      -
        id: upcoming-card-taxonomies
        type: card
        enabled: true
        image: alan-jiang-md3fl3s4z3s-unsplash.jpg
        title: 'Taxonomy pages'
        text: 'Category and tag archive pages, rendered through Vue just like entries are today, complete with a TermTransformer and a paginated listing endpoint for pages like /blog/category/design.'
      -
        id: upcoming-card-globals
        type: card
        enabled: true
        image: viktor-forgacs-click-vng9kgg_era-unsplash.jpg
        title: 'Global sets'
        text: 'Scoped per site, per collection, or per blueprint, and exposed through an explicit whitelist, where even a wildcard entry is still a deliberate choice, not an accident.'
      -
        id: upcoming-card-forms
        type: card
        enabled: true
        image: evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
        title: 'Native forms'
        text: "Statamic Forms wired up to Inertia's useForm(), with validation errors flowing back to Vue exactly the way they do for any other form, CSRF and honeypot included."
      -
        id: upcoming-card-search
        type: card
        enabled: true
        image: leon-rohrwild-5apkpfvda8i-unsplash.jpg
        title: 'Site search'
        text: "A lightweight search endpoint sitting alongside the entry listing API, so a Vue search box can query Statamic's own Search index without a full page reload."
  -
    id: upcoming-text-alsobrewing
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'A few more things simmering on the same list'
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Taxonomies, globals, forms, and search are the four we are most excited about, but the roadmap file has more entries than that. A handful of smaller ideas are sitting in the same document, in no particular order:'
      -
        type: bulletList
        content:
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: 'A dedicated GlideImage.vue component, so every block that currently hand-writes its own img tag with srcset and sizes gets a single reusable component instead.'
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: "A proper Inertia-rendered error page for 404s and 500s, instead of falling back to Laravel's plain HTML error page outside the Vue shell."
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: 'Redirect support, so a URL that used to resolve to an entry and was since redirected does not just quietly 404.'
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: 'Multi-site support, covering how a language switcher resolves the equivalent entry on another site and updates the shared props without a full reload.'
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: 'Generated TypeScript types straight from blueprint YAML, so a renamed field cannot silently turn into an undefined prop somewhere in a Vue component.'
          -
            type: listItem
            content:
              -
                type: paragraph
                content:
                  -
                    type: text
                    text: 'A proper feature-test suite, so all of the above can be built with a safety net instead of by hand-testing in a browser every time.'
      -
        type: paragraph
        content:
          -
            type: text
            text: "None of these are urgent on their own, but taken together they are the difference between a bridge that works today and one that stays maintainable as more of the site's content moves through it."
  -
    id: upcoming-quote-001
    type: quote
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Nothing here is committed or scheduled. This is a collection point, not a spec.'
    author: 'The addon roadmap, keeping us honest'
  -
    id: upcoming-gallery-001
    type: masonry_gallery
    enabled: true
    column_count: three
    images:
      - alan-jiang-md3fl3s4z3s-unsplash.jpg
      - evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
      - leon-rohrwild-5apkpfvda8i-unsplash.jpg
      - marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
      - tsuyoshi-kozu-boc-f7jwdek-unsplash.jpg
      - viktor-forgacs-click-vng9kgg_era-unsplash.jpg
  -
    id: upcoming-accordion-001
    type: accordion
    enabled: true
    items:
      -
        id: upcoming-faq-001
        type: item
        enabled: true
        question: 'When are these features shipping?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'There is no fixed date attached to any of them, deliberately. The roadmap file itself says as much: it is a collection point, not a spec. Each idea gets built once a real page on this site actually needs it, rather than being scheduled in advance and then bent to fit whatever gets built. That has been true of everything shipped so far, the page-builder blocks, the entry-listing API, even this Load more button, so there is no reason to expect the next batch of features to arrive any differently.'
      -
        id: upcoming-faq-002
        type: item
        enabled: true
        question: 'Can I use any of this today?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Entries, the full page-builder block set demonstrated on this very post, and the entry-listing API behind its Load more button are already built and in daily use across this site. Taxonomies, global sets, native forms, and search are the next layer on top of that foundation, not a replacement for it, so nothing described above changes how any of the existing blocks or the entry API already work.'
      -
        id: upcoming-faq-003
        type: item
        enabled: true
        question: 'Will this replace the Statamic Control Panel?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'No, and that is intentional. Every idea on this roadmap is designed around the same split that already exists for everything else on this site: editors keep writing and publishing content through the ordinary Statamic Control Panel, and only the public-facing rendering happens through Vue. A taxonomy term, a global set, or a form will all be edited exactly the way any other Statamic content is edited today.'
      -
        id: upcoming-faq-004
        type: item
        enabled: true
        question: 'Why build all of this as an addon instead of directly in the project?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Keeping it as a reusable addon rather than one-off application code means every one of these features benefits any project built on the same Statamic-plus-Inertia foundation, not just this site. It also forces a slightly higher bar before something gets built: a feature has to be generic enough to make sense as addon code, not just a shortcut for this particular blog.'
      -
        id: upcoming-faq-006
        type: item
        enabled: true
        question: 'Why scope globals per site, collection, or blueprint instead of just sharing them all?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: "Because not every page needs every global, and Inertia ships its shared props on every single visit, so an unscoped global becomes weight every visitor pays for on every page. Scoping by site, collection, or blueprint, combined with an explicit whitelist of which global handles are exposed at all, keeps a global's reach limited to the pages that actually asked for it instead of leaking into the rest of the site by default. A wildcard entry in that whitelist does not weaken the guarantee, since typing "
              -
                type: text
                marks:
                  -
                    type: bold
                text: '*'
              -
                type: text
                text: ' for a global set is just as much an explicit decision as naming its fields one by one. What the whitelist rules out is a global reaching a page nobody configured it for, not a global exposing more than a handful of fields.'
      -
        id: upcoming-faq-005
        type: item
        enabled: true
        question: 'Where can I follow along?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: "The roadmap itself lives in the addon's own repository as a plain markdown file. Once an idea moves from sketch to shipped feature, it graduates out of that file and into the addon's proper documentation, the same way the entry-listing API and the page-builder blocks already have."
  -
    id: upcoming-text-002
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Every one of the features above starts from the same premise the rest of this addon already proved out: Statamic can stay exactly as good an authoring experience as it has always been, while the public side of the site is a proper, modern Vue application underneath. Taxonomies, globals, forms, and search are just the next four places to apply that same idea.'
      -
        type: paragraph
        content:
          -
            type: text
            marks:
              -
                type: bold
            text: 'If you have an opinion on which of these should come first, that is exactly the kind of feedback the roadmap exists to collect.'
author: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_by: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_at: 1783719397
---
