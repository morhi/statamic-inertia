---
id: 00b7f7cd-a228-46d2-99ab-3cd625a13ea0
blueprint: blog
title: 'The birth of the Statamic Inertia Addon'
author: dec72713-dc5a-45dc-9f82-4035fe14663d
content_blocks:
  -
    id: birth-hero-001
    type: hero
    enabled: true
    title: 'The birth of the Statamic Inertia Addon'
    subtitle: 'Why we swapped Antlers for Vue, and kept Statamic as the source of truth.'
    background_image: marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
    cta_label: ''
    cta_url: ''
  -
    id: birth-text-001
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'This site is built on Statamic, but you will not find a single Antlers template rendering its pages. Every request is handed off to a Vue 3 application through '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Inertia.js'
          -
            type: text
            text: ', with Statamic acting purely as the content and editing layer underneath.'
      -
        type: paragraph
        content:
          -
            type: text
            text: 'The goal was simple to state and harder to get right: keep Statamic''s flat-file simplicity and beautiful Control Panel, but let the public-facing site be a proper component-driven Vue app, without standing up a separate REST or GraphQL API just to feed it.'
  -
    id: birth-quote-001
    type: quote
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Statamic serves as the data layer. Vue handles all rendering.'
    author: 'Project motto'
    author_image: ''
  -
    id: birth-accordion-001
    type: accordion
    enabled: true
    items:
      -
        id: birth-accordion-item-001
        type: item
        enabled: true
        question: 'Why not just use Antlers templates?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Antlers is great for content-driven pages, but we wanted a proper component model, client-side interactivity, and the same Vue skills our team already uses elsewhere — without giving up Statamic''s editing experience.'
      -
        id: birth-accordion-item-002
        type: item
        enabled: true
        question: 'Why Inertia instead of a full SPA with its own API?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'A separate API would mean maintaining two representations of the same content. Inertia lets the Vue app receive page data directly from Laravel controllers, so Statamic stays the single source of truth.'
      -
        id: birth-accordion-item-003
        type: item
        enabled: true
        question: 'Does this replace the Statamic Control Panel?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Not at all. Editors still use the normal Statamic CP to write and publish content. Only the public-facing frontend renders through Vue instead of Antlers.'
updated_by: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_at: 1783683670
---
