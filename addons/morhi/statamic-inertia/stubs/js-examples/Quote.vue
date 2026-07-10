<template>
  <div class="block-quote py-24 px-8">
    <div class="max-w-5xl mx-auto relative">
      <!-- Glass panel -->
      <div class="relative rounded-3xl bg-white/[0.04] backdrop-blur-xl border border-white/[0.07] px-12 py-16 overflow-hidden">
        <!-- Decorative quote mark -->
        <span class="absolute -top-4 left-8 text-[12rem] leading-none font-serif text-white/[0.04] select-none pointer-events-none">&ldquo;</span>

        <blockquote class="relative z-10">
          <div
            v-if="content"
            class="text-2xl lg:text-3xl text-gray-100 leading-relaxed font-light italic mb-10 prose prose-invert max-w-none"
            v-html="content"
          />

          <footer class="flex items-center gap-5">
            <img
              v-if="author_image?.url"
              :src="author_image.url"
              :alt="author"
              class="w-11 h-11 rounded-full object-cover ring-2 ring-white/10"
            />
            <div>
              <div class="w-6 h-px bg-sky-400 mb-2" />
              <cite v-if="author" class="not-italic text-sm font-medium text-gray-300 tracking-wider uppercase">
                {{ author }}
              </cite>
            </div>
          </footer>
        </blockquote>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

defineProps<{
  content?: string
  author?: string
  author_image?: AssetField
}>()
</script>
