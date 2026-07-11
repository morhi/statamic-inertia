<template>
  <footer class="border-t border-white/10 bg-[#080810] px-8 py-16">
    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-12 sm:grid-cols-3">
      <div v-if="footer">
        <h3 class="text-sm font-semibold tracking-widest uppercase text-white/80">{{ footer.company_name }}</h3>
        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-gray-400">{{ footer.company_address }}</p>
      </div>

      <div v-if="footer?.social_links?.length">
        <h3 class="text-sm font-semibold tracking-widest uppercase text-white/80">Connect</h3>
        <ul class="mt-4 space-y-2">
          <li v-for="link in footer.social_links" :key="link.id">
            <a
              :href="link.url"
              :target="link.external ? '_blank' : undefined"
              :rel="link.external ? 'noopener noreferrer' : undefined"
              class="text-sm text-gray-400 hover:text-sky-400 transition-colors"
            >
              {{ link.platform }}
            </a>
          </li>
        </ul>
      </div>

      <div v-if="footer?.newsletter_label">
        <h3 class="text-sm font-semibold tracking-widest uppercase text-white/80">From the Blog</h3>
        <p class="mt-4 text-sm leading-relaxed text-gray-400">{{ footer.newsletter_label }}</p>
        <Link
          v-if="footer.newsletter_cta_url"
          :href="footer.newsletter_cta_url"
          class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-sky-400 hover:text-sky-300 transition-colors"
        >
          <span>Subscribe</span>
          <span>&rarr;</span>
        </Link>
      </div>
    </div>
  </footer>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { useInertiaPageProp } from '../utils/useInertiaPageProp';

interface SocialLink {
  id?: string
  platform: string
  url: string
  icon: string
  external: boolean
}

interface FooterGlobals {
  company_name?: string
  company_address?: string
  social_links?: SocialLink[]
  newsletter_label?: string
  newsletter_cta_url?: string
}

const globals = useInertiaPageProp<{ footer?: FooterGlobals }>('globals');
const footer = globals?.footer;
</script>
