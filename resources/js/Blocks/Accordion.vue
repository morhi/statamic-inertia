<template>
  <div class="block-accordion py-20 px-8">
    <div class="max-w-3xl mx-auto">
      <div class="rounded-2xl bg-white/[0.03] backdrop-blur-xl border border-white/[0.07] overflow-hidden divide-y divide-white/[0.06]">
        <div v-for="(item, index) in items" :key="item.id">
          <button
            class="w-full text-left flex items-center justify-between px-8 py-6 text-white hover:bg-white/[0.04] transition-colors duration-200"
            @click="toggle(index)"
          >
            <span class="font-medium text-base pr-8">{{ item.question }}</span>
            <span
              class="shrink-0 w-6 h-6 rounded-full border flex items-center justify-center text-xs transition-all duration-300"
              :class="open === index ? 'border-sky-400 text-sky-400 rotate-45' : 'border-white/20 text-white/40'"
            >+</span>
          </button>

          <div v-if="open === index" class="border-l-2 border-sky-400 mx-8 mb-6">
            <div
              class="pl-6 py-1 text-gray-400 text-sm leading-relaxed prose prose-invert prose-sm max-w-none"
              v-html="item.answer"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

defineOptions({ inheritAttrs: false })

interface AccordionItem {
  id?: string
  question?: string
  answer?: string
}

defineProps<{
  items?: AccordionItem[]
}>()

const open = ref<number | null>(null)

const toggle = (index: number) => {
  open.value = open.value === index ? null : index
}
</script>
