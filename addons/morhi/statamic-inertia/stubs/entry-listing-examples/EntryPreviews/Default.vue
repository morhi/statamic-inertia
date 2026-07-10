<template>
  <Link :href="entry.url" class="block-entry-listing-default group py-4 border-b border-white/[0.07] flex items-baseline justify-between gap-4">
    <h3 class="text-white font-medium group-hover:text-sky-400 transition-colors duration-200">{{ entry.title }}</h3>
    <time v-if="entry.date" :datetime="entry.date" class="text-gray-500 text-sm shrink-0">{{ entry.date }}</time>
  </Link>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3'

defineOptions({ inheritAttrs: false })

interface ListedEntry {
  id: string
  title: string
  url: string
  date?: string
  preview?: Record<string, any>
}

defineProps<{
  entry: ListedEntry
}>()
</script>
