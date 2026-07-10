<template>
  <div class="block-card-grid py-20 px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 max-w-6xl mx-auto">
      <a
        v-for="card in cards"
        :key="card.id"
        :href="card.link || '#'"
        class="group relative rounded-2xl overflow-hidden bg-white/[0.04] backdrop-blur-sm border border-white/[0.07] hover:border-sky-400/30 hover:bg-white/[0.07] transition-all duration-300 flex flex-col"
      >
        <!-- Image -->
        <div class="relative overflow-hidden h-52">
          <img
            v-if="card.image?.url"
            :src="card.image.url"
            :srcset="card.image.srcset"
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
            :alt="card.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-[#080810]/80 to-transparent" />
        </div>

        <!-- Content -->
        <div class="p-6 flex flex-col flex-1">
          <h3 v-if="card.title" class="text-base font-semibold text-white mb-2">{{ card.title }}</h3>
          <p v-if="card.text" class="text-gray-400 text-sm leading-relaxed flex-1">{{ card.text }}</p>
          <div class="mt-5 flex items-center gap-2 text-sky-400 text-sm font-medium">
            <span>Read more</span>
            <span class="group-hover:translate-x-1 transition-transform duration-200">&rarr;</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</template>

<script lang="ts" setup>
defineOptions({ inheritAttrs: false })

interface CardItem {
  id?: string
  image?: AssetField
  title?: string
  text?: string
  link?: string
}

defineProps<{
  cards?: CardItem[]
}>()
</script>
