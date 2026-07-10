<template>
  <div class="block-masonry-gallery py-24 px-8">
    <div :class="containerClasses" id="masonry-container">
      <img
        v-for="(image, index) in images"
        :key="index"
        :src="image.thumb_url"
        :srcset="image.srcset"
        sizes="(max-width: 768px) 100vw, (max-width: 1280px) 50vw, 33vw"
        :alt="image.alt || ''"
        loading="lazy"
        class="w-full cursor-zoom-in rounded-xl opacity-0 translate-y-4 transition-all duration-700 [break-inside:avoid] mb-4 masonry-item"
        @load="onImageLoad(index, $event)"
        @click="openLightbox(index)"
      />
    </div>

    <Transition name="fade">
      <Teleport to="body">
        <div v-if="lightboxOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" @click.self="closeLightbox">
          <button class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors" @click.stop="closeLightbox">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M18 6L6 18M6 6l12 12"/></svg>
          </button>

          <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors p-2" @click.stop="prev">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/></svg>
          </button>

          <div class="relative w-[90vw] h-[80vh] overflow-hidden">
            <Transition :name="slideDirection === 'next' ? 'slide-next' : 'slide-prev'">
              <img v-if="currentSrc" :key="currentIndex" :src="currentSrc" :alt="currentAlt" class="absolute inset-0 w-full h-full object-contain rounded-xl shadow-2xl" @click.stop />
            </Transition>
          </div>

          <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors p-2" @click.stop="next">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/></svg>
          </button>

          <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/50 text-sm font-medium">{{ currentIndex + 1 }} / {{ images.length }}</div>
        </div>
      </Teleport>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps<{
  column_count?: string
  images?: AssetField[]
}>();

const lightboxOpen = ref(false);
const currentIndex = ref(0);
const slideDirection = ref<'next' | 'prev'>('next');
let keyboardListener: ((e: KeyboardEvent) => void) | null = null;

const containerClasses = computed(() => {
  const cols = props.column_count === 'two' ? 'columns-2' : props.column_count === 'four' ? 'columns-4' : 'columns-3';
  return `${cols} gap-y-4 [break-inside:avoid] mb-4 masonry-item`.trim();
});

const getImageList = () => props.images ?? [];

const currentSrc = computed(() => {
  const list = getImageList();
  if (!list.length) return '';
  return list[currentIndex.value]?.url || '';
});

const currentAlt = computed(() => getImageList()[currentIndex.value]?.alt || '');

function openLightbox(index: number) {
  currentIndex.value = index;
  lightboxOpen.value = true;
}

function closeLightbox() {
  lightboxOpen.value = false;
}

function prev() {
  const len = getImageList().length;
  if (len === 0) return;
  slideDirection.value = 'prev';
  currentIndex.value = (currentIndex.value - 1 + len) % len;
}

function next() {
  const len = getImageList().length;
  if (len === 0) return;
  slideDirection.value = 'next';
  currentIndex.value = (currentIndex.value + 1) % len;
}

function onImageLoad(index: number, e: Event) {
  const el = e.target as HTMLElement;
  if (!el || el.classList.contains('opacity-100')) return;
  el.classList.remove('opacity-0', 'translate-y-4');
  el.classList.add('opacity-100', 'translate-y-0');
}

onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => entries.forEach((entry) => {
      if (entry.isIntersecting && !(entry.target as HTMLElement).classList.contains('opacity-100')) {
        onImageLoad(-99, {target: entry.target} as unknown as Event);
        observer.unobserve(entry.target);
      }
    }),
    { rootMargin: '-5% 0px -20% 0px' }
  );

  nextTick(() => {
    document.querySelectorAll('#masonry-container .masonry-item').forEach(el => observer.observe(el));
  });

  keyboardListener = (e: KeyboardEvent) => {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
  };

  window.addEventListener('keyup', keyboardListener);
});

onUnmounted(() => {
  if (keyboardListener) window.removeEventListener('keyup', keyboardListener);
});
</script>

<style scoped>
.columns-2 { columns: 2; }
.columns-3 { columns: 3; }
.columns-4 { columns: 4; }

@keyframes fade-in {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.masonry-item.loaded { animation: fade-in 0.5s ease forwards; }

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.1s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-next-enter-active,
.slide-next-leave-active,
.slide-prev-enter-active,
.slide-prev-leave-active {
  position: absolute;
  inset: 0;
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.slide-next-enter-from { transform: translateX(48px); opacity: 0; }
.slide-next-leave-to { transform: translateX(-48px); opacity: 0; }

.slide-prev-enter-from { transform: translateX(-48px); opacity: 0; }
.slide-prev-leave-to { transform: translateX(48px); opacity: 0; }
</style>
