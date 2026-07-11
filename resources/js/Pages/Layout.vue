<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Footer from '../Components/Footer.vue';
import { useInertiaPageProp } from '../utils/useInertiaPageProp';
import { usePreviewRefresh } from '../utils/usePreviewRefresh';

const nav = useInertiaPageProp<Array<{ label: string; href: string }>>('nav');
const globals = useInertiaPageProp<{ general?: { site_name: string } }>('globals');
const siteName = globals?.general?.site_name ?? 'Studio';

usePreviewRefresh();
</script>

<template>
  <div class="layout min-h-screen bg-[#080810] text-white antialiased">
    <header
      class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-8 py-5 backdrop-blur-sm bg-[#080810]/70 border-b border-white/10">
      <Link href="/"
        class="text-sm font-semibold tracking-widest uppercase text-white/80 hover:text-white transition-colors">
        {{ siteName }}
      </Link>
      <nav class="flex items-center gap-8">
        <Link v-for="item in nav" :key="item.href" :href="item.href"
          class="text-sm text-white/60 hover:text-white transition-colors">
          {{ item.label }}
        </Link>
      </nav>
    </header>
    <div class="pt-[61px]">
      <slot />
    </div>
    <Footer />
  </div>
</template>
