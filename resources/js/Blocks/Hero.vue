<template>
  <div
    class="block-hero relative min-h-screen flex items-end bg-[#080810] overflow-hidden"
    :style="background_image?.url ? `background-image: url(${background_image.url}); background-size: cover; background-position: center;` : ''"
  >
    <!-- Gradient overlay: heavy at bottom, lighter at top -->
    <div class="absolute inset-0 bg-gradient-to-t from-[#080810] via-[#080810]/60 to-[#080810]/10" />

    <!-- Content anchored bottom-left -->
    <div class="relative z-10 px-10 pb-20 pt-40 max-w-4xl">
      <!-- Accent bar -->
      <div class="w-12 h-px bg-sky-400 mb-8" />

      <h1 v-if="title" class="text-6xl lg:text-8xl font-bold tracking-tight leading-none text-white mb-6">
        {{ title }}
      </h1>

      <p v-if="subtitle" class="text-lg text-gray-400 max-w-xl mb-10 leading-relaxed">
        {{ subtitle }}
      </p>

      <a
        v-if="cta_label && cta_url"
        :href="cta_url"
        class="inline-flex items-center gap-3 border border-white/20 text-white px-7 py-3 rounded-full text-sm font-medium tracking-wide backdrop-blur-sm bg-white/5 hover:bg-white/10 hover:border-white/40 transition-all duration-300"
      >
        {{ cta_label }}
        <span class="text-sky-400">&rarr;</span>
      </a>
    </div>
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  title?: string
  subtitle?: string
  background_image?: AssetField
  cta_label?: string
  cta_url?: string
}>()
</script>
