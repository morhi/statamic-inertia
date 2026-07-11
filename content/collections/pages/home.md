---
id: home
blueprint: pages
title: Homepage
template: home
author: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_by: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_at: 1783719061
content_blocks:
  -
    id: hero-block-001
    type: hero
    enabled: true
    title: 'Building the modern web'
    subtitle: 'A showcase of Statamic + Inertia + Vue — fast, flexible, and beautifully composed.'
    background_image: viktor-forgacs-click-vng9kgg_era-unsplash.jpg
    cta_label: "See what's possible"
    cta_url: '#'
  -
    id: entry-listing-block-001
    type: entry_listing
    enabled: true
    heading: 'From the blog'
    collection: blog
    per_page: 2
  -
    id: quote-block-001
    type: quote
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'The best way to predict the future is to invent it.'
    author: 'Alan Kay'
    author_image: leon-rohrwild-5apkpfvda8i-unsplash.jpg
  -
    id: bpMbIh6n74f2hmnK7Um_q
    column_count: three
    images:
      - alan-jiang-md3fl3s4z3s-unsplash.jpg
      - evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
      - leon-rohrwild-5apkpfvda8i-unsplash.jpg
      - marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
      - tsuyoshi-kozu-boc-f7jwdek-unsplash.jpg
      - viktor-forgacs-click-vng9kgg_era-unsplash.jpg
    type: masonry_gallery
    enabled: true
  -
    id: mBd05_5VrlbGr6G4hcPVY
    heading: 'Even more from the blog'
    collection: blog
    per_page: 2
    type: entry_listing
    enabled: true
  -
    id: card-grid-block-001
    type: card_grid
    enabled: true
    cards:
      -
        id: card-001
        type: card
        enabled: true
        image: alan-jiang-md3fl3s4z3s-unsplash.jpg
        title: 'Statamic CMS'
        text: 'A powerful flat-file CMS built on Laravel with a beautiful control panel.'
        link: 'https://statamic.com'
      -
        id: card-002
        type: card
        enabled: true
        image: marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
        title: Inertia.js
        text: 'Build single-page apps without building an API. The modern monolith.'
        link: 'https://inertiajs.com'
      -
        id: card-003
        type: card
        enabled: true
        image: evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
        title: 'Vue 3'
        text: 'The progressive JavaScript framework for building reactive user interfaces.'
        link: 'https://vuejs.org'
  -
    id: accordion-block-001
    type: accordion
    enabled: true
    items:
      -
        id: accordion-item-001
        type: item
        enabled: true
        question: 'What is Statamic?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Statamic is a Laravel-based CMS that stores content as flat files. No database required — just clean YAML and Markdown.'
      -
        id: accordion-item-002
        type: item
        enabled: true
        question: 'Why use Inertia.js with Statamic?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Inertia lets you write Vue components as full page views while keeping all your routing and data fetching on the server — no REST API needed.'
      -
        id: accordion-item-003
        type: item
        enabled: true
        question: 'How does the block builder work?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Each block is a replicator set in Statamic. The transformer converts field values to JSON-serializable data, and Vue components render each block type by its type handle.'
  -
    id: image-caption-block-001
    type: image_caption
    enabled: true
    image: tsuyoshi-kozu-boc-f7jwdek-unsplash.jpg
    caption: 'A beautiful scene — powered by Statamic assets.'
    alignment: center
---
