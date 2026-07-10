<template>
  <div class="block-entry-listing py-20 px-8">
    <div class="max-w-3xl mx-auto">
      <h2 v-if="heading" class="text-2xl font-semibold text-white mb-8">{{ heading }}</h2>

      <div class="flex flex-col">
        <component
          :is="previewFor(collection)"
          v-for="entry in items"
          :key="entry.id"
          :entry="entry"
        />
      </div>

      <div v-if="hasMore" class="mt-8 flex justify-center">
        <button
          type="button"
          :disabled="loading"
          class="px-5 py-2.5 rounded-full border border-white/[0.15] text-white text-sm font-medium hover:border-sky-400/40 hover:bg-white/[0.05] transition-colors duration-200 disabled:opacity-50"
          @click="loadMore"
        >
          {{ loading ? 'Loading…' : 'Load more' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import type { Component } from 'vue'

interface ListedEntry {
  id: string
  title: string
  url: string
  date?: string
  preview?: Record<string, any>
}

const props = defineProps<{
  heading?: string
  collection: string
  per_page: number
  entries: ListedEntry[]
  next_page: number | null
  has_more: boolean
  load_more_url: string
}>()

defineOptions({ inheritAttrs: false })

// Auto-discover per-collection preview components, falling back to Default.vue.
// Filename convention: PascalCase → snake_case handle (e.g. Blog.vue → blog), mirroring Blocks.vue.
const previewModules = import.meta.glob('../Components/EntryPreviews/*.vue', { eager: true }) as Record<string, { default: Component }>

const previewMap: Record<string, Component> = Object.fromEntries(
  Object.entries(previewModules).map(([path, mod]) => {
    const name = path.replace('../Components/EntryPreviews/', '').replace('.vue', '')
    const handle = name.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '')
    return [handle, mod.default]
  })
)

const previewFor = (collection: string): Component => previewMap[collection] ?? previewMap['default']

const items = ref<ListedEntry[]>([...props.entries])
const nextPage = ref<number | null>(props.next_page)
const hasMore = ref<boolean>(props.has_more)
const loading = ref(false)

const loadMore = async () => {
  if (loading.value || !hasMore.value || !nextPage.value) return

  loading.value = true

  try {
    const url = new URL(props.load_more_url, window.location.origin)
    url.searchParams.set('collection', props.collection)
    url.searchParams.set('page', String(nextPage.value))
    url.searchParams.set('per_page', String(props.per_page))

    const response = await fetch(url.toString(), {
      headers: { Accept: 'application/json' },
    })

    const data = await response.json()

    items.value.push(...data.entries)
    nextPage.value = data.next_page
    hasMore.value = data.has_more
  } finally {
    loading.value = false
  }
}
</script>
