---
id: 197dbb5d-9468-4243-b1f0-077693589ba6
blueprint: blog
title: 'Handling SSR'
author: dec72713-dc5a-45dc-9f82-4035fe14663d
content_blocks:
  -
    id: ssr-hero-001
    type: hero
    enabled: true
    title: 'Handling SSR'
    subtitle: 'How a Node process keeps the first paint fast, and what happens when it goes down.'
    background_image: viktor-forgacs-click-vng9kgg_era-unsplash.jpg
    cta_label: ''
    cta_url: ''
  -
    id: ssr-text-001
    type: text
    enabled: true
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'A Vue single-page app is great once it is running in the browser, but the very first request still needs something to send back before any JavaScript has executed. That is what server-side rendering (SSR) is for: a small Node process pre-renders the Vue app into real HTML, so the first paint is fast and the page is readable even before hydration kicks in.'
  -
    id: ssr-card-grid-001
    type: card_grid
    enabled: true
    cards:
      -
        id: ssr-card-001
        type: card
        enabled: true
        image: alan-jiang-md3fl3s4z3s-unsplash.jpg
        title: 'Full page load'
        text: 'A browser request for a URL is handed to Laravel, which asks the Node SSR service to render the Vue app for that page and returns the resulting HTML already in place.'
        link: ''
      -
        id: ssr-card-002
        type: card
        enabled: true
        image: evgeni-tcherkasski-kow5jlc9ipi-unsplash.jpg
        title: 'Client hydration'
        text: 'The browser receives fully-rendered markup and Vue quietly attaches itself to it — no flash of empty content, no layout shift.'
        link: ''
      -
        id: ssr-card-003
        type: card
        enabled: true
        image: leon-rohrwild-5apkpfvda8i-unsplash.jpg
        title: 'Inertia navigation'
        text: 'Every click after that only fetches JSON. The layout persists, only the page component swaps, and the Node SSR server is never involved again.'
        link: ''
  -
    id: ssr-image-caption-001
    type: image_caption
    enabled: true
    image: tsuyoshi-kozu-boc-f7jwdek-unsplash.jpg
    caption: 'Keeping the SSR bundle in sync with the client build is what keeps this picture from turning into a blank page.'
    alignment: center
  -
    id: ssr-accordion-001
    type: accordion
    enabled: true
    items:
      -
        id: ssr-accordion-item-001
        type: item
        enabled: true
        question: 'What happens if the SSR server is not running?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'Inertia gracefully falls back to client-side rendering. The page still works, it just will not have pre-rendered markup on the very first paint until the SSR server is back.'
      -
        id: ssr-accordion-item-002
        type: item
        enabled: true
        question: 'Why did my new component not show up after I changed it?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'The SSR bundle is a separate compiled artifact from the client build. Both need to be rebuilt, and the SSR process restarted, whenever a Vue component changes — otherwise the two can drift apart and cause hydration mismatches.'
      -
        id: ssr-accordion-item-003
        type: item
        enabled: true
        question: 'Does SSR affect page-to-page navigation?'
        answer:
          -
            type: paragraph
            content:
              -
                type: text
                text: 'No. SSR only renders the very first full page load. Every navigation after that is a plain JSON response and never touches the Node server at all.'
updated_by: dec72713-dc5a-45dc-9f82-4035fe14663d
updated_at: 1783687388
---
