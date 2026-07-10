import { createSSRApp, h } from 'vue'
import { createInertiaApp, router } from '@inertiajs/vue3'

// Force a full page reload instead of showing Inertia's raw-HTML error modal
// when a request expecting an X-Inertia JSON response gets back plain HTML
// (e.g. a stale browser-cached page).
router.on('invalid', (event) => {
  event.preventDefault()
  window.location.href = event.detail.response.request.responseURL || window.location.href
})

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    // createSSRApp is required for client-side hydration of server-rendered HTML.
    createSSRApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
