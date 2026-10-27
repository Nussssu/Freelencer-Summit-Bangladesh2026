<script setup lang="ts">
import { ref } from 'vue';
import Icon from './Icon.vue';

// Plus-icon accordion (Take Off FAQ, T11). Buttons inside headings; one panel open at a time.
const props = defineProps<{ items: { q: string; a: string }[]; idPrefix: string; headingLevel?: 'h3' | 'h4' }>();
const open = ref<number | null>(null);
</script>

<template>
    <ul class="border-t border-current/15">
        <li v-for="(item, i) in props.items" :key="item.q" class="border-b border-current/15">
            <component :is="headingLevel ?? 'h3'">
                <button type="button" class="flex min-h-14 w-full items-center gap-4 py-4 text-left text-lg font-bold" :aria-expanded="open === i" :aria-controls="`${idPrefix}-${i}`" :id="`${idPrefix}-btn-${i}`" @click="open = open === i ? null : i">
                    {{ item.q }}
                    <Icon name="plus" class="ml-auto shrink-0 transition-transform duration-300" :class="open === i && 'rotate-45'" />
                </button>
            </component>
            <div :id="`${idPrefix}-${i}`" role="region" :aria-labelledby="`${idPrefix}-btn-${i}`" class="grid transition-[grid-template-rows] duration-300 ease-out-expo" :class="open === i ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                <div class="overflow-hidden" :inert="open !== i">
                    <p class="max-w-2xl pb-5">{{ item.a }}</p>
                </div>
            </div>
        </li>
    </ul>
</template>
