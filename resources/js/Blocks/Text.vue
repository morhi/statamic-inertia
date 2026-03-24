<template>
  <div class="block-text px-8 py-12 max-w-3xl mx-auto">
    <div class="prose prose-invert prose-lg prose-p:text-gray-300 prose-headings:text-white prose-a:text-sky-400" v-html="content" />
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  content?: string
}>()
</script>
