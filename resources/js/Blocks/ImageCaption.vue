<template>
  <div class="block-image-caption py-16 px-8">
    <figure class="relative max-w-5xl mx-auto">
      <div class="relative rounded-2xl overflow-hidden">
        <img
          v-if="image?.url"
          :src="image.url"
          :srcset="image.srcset"
          sizes="(max-width: 1280px) 100vw, 1280px"
          :alt="caption"
          class="w-full object-cover"
        />
        <!-- Caption as frosted glass strip over the image bottom -->
        <figcaption
          v-if="caption"
          class="absolute bottom-0 inset-x-0 px-6 py-4 bg-black/40 backdrop-blur-md border-t border-white/[0.08]"
        >
          <p class="text-sm text-gray-300 tracking-wide">{{ caption }}</p>
        </figcaption>
      </div>
    </figure>
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  image?: AssetField
  caption?: string
  alignment?: 'left' | 'center' | 'right'
}>()
</script>
