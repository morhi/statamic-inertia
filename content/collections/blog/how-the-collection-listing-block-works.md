---
id: 9b235701-8d2b-4e88-8dc3-ce305156d98d
blueprint: blog
title: 'How the collection listing block works'
author: dec72713-dc5a-45dc-9f82-4035fe14663d
content_blocks:
  -
    id: listing-hero-001
    type: hero
    enabled: true
    title: 'How the collection listing block works'
    subtitle: 'Pick a collection, set a heading, and get a paginated list with a working Load more button.'
    background_image: evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
    cta_label: ''
    cta_url: ''
  -
    id: listing-text-001
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'The '
          -
            type: text
            marks:
              -
                type: bold
            text: 'Entry Listing'
          -
            type: text
            text: ' page-builder block lets an editor drop a paginated list of entries onto any page: pick a collection, set a heading and how many items to show per page, and get a working "Load more" button — without a developer writing any custom code for that collection.'
  -
    id: listing-card-grid-001
    type: card_grid
    enabled: true
    cards:
      -
        id: listing-card-001
        type: card
        enabled: true
        image: viktor-forgacs-click-vng9kgg_era-unsplash.jpg
        title: 'Pick a collection'
        text: 'The block itself only has three fields: a collection picker, an optional heading, and how many entries to show per page.'
        link: ''
      -
        id: listing-card-002
        type: card
        enabled: true
        image: marek-piwnicki-j9rf6ctw_q0-unsplash.jpg
        title: 'The first page ships with the page'
        text: 'The initial batch of entries is queried when the page itself is rendered, so it is there immediately — no loading spinner, and it benefits from the same page cache as everything else.'
        link: ''
      -
        id: listing-card-003
        type: card
        enabled: true
        image: alan-jiang-md3fl3s4z3s-unsplash.jpg
        title: '"Load more" fetches live'
        text: 'Clicking Load more calls a small dedicated endpoint that is deliberately kept outside the page cache, so it always returns whatever exists right now.'
        link: ''
  -
    id: listing-accordion-001
    type: accordion
    enabled: true
    items:
      -
        id: listing-accordion-item-001
        type: item
        enabled: true
        question: 'Can "Load more" ever show stale results?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'No — that endpoint is intentionally never cached, so every click reflects whatever is published at that moment, even if the page around it is served from cache.'
      -
        id: listing-accordion-item-002
        type: item
        enabled: true
        question: 'What do I need to set up for a new collection?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Nothing. The block works for any collection out of the box. You would only add anything extra if that collection''s entries deserve a differently styled card than the default.'
      -
        id: listing-accordion-item-003
        type: item
        enabled: true
        question: 'How does the cache know to update when I publish a new post?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Publishing, editing, or deleting an entry in a listed collection automatically invalidates any cached page that shows that listing, so visitors never end up looking at an outdated list.'
  -
    id: listing-quote-001
    type: quote
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'A cached page is only as good as its invalidation.'
    author: 'Project motto'
    author_image: ''
updated_by: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_at: 1783687392
---
