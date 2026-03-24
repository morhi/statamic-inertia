<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Layout from '../Layout.vue'
import Blocks from '../../Components/Blocks.vue';

defineOptions({ layout: Layout })

const props = defineProps<{
  entry: {
    id: string
    url: string
    slug: string
    collection: string
    blueprint: string
    data: Record<string, unknown>
  }
}>()
</script>

<template>
  <Head>
    <title>{{ entry.data.title as string }}</title>
  </Head>

  <main>
    <Blocks :blocks="entry.data.content_blocks" />
  </main>
</template>
