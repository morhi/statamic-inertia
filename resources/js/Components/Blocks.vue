<template>
  <div class="blocks">
    <template v-for="block in blocks" :key="block.id">
      <component
        :is="blockMap[block.type]"
        v-if="blockMap[block.type]"
        v-bind="block"
      />
    </template>
  </div>
</template>

<script lang="ts" setup>
import { PropType } from 'vue';
import type { Component } from 'vue';

// Auto-discover all Blocks/*.vue files. No changes needed here when adding new blocks.
// Filename convention: PascalCase → snake_case handle (e.g. CardGrid.vue → card_grid)
const modules = import.meta.glob('../Blocks/*.vue', { eager: true }) as Record<string, { default: Component }>;

const blockMap: Record<string, Component> = Object.fromEntries(
  Object.entries(modules).map(([path, mod]) => {
    const name   = path.replace('../Blocks/', '').replace('.vue', '');
    const handle = name.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '');
    return [handle, mod.default];
  })
);

defineProps({
  blocks: Array as PropType<Blocks>,
});
</script>
