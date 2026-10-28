<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import type { PageMeta } from '@/types/inner';

// Previous / numbered / next. Emits the page; useDirectory syncs it to the URL.
const props = defineProps<{ meta: PageMeta }>();
const emit = defineEmits<{ go: [page: number] }>();
const { t } = useI18n();
const pages = computed(() => {
    const { page, lastPage } = props.meta;
    const set = new Set([1, lastPage, page - 1, page, page + 1].filter((p) => p >= 1 && p <= lastPage));
    return [...set].sort((a, b) => a - b);
});
const btn = 'grid min-h-11 min-w-11 place-items-center border border-line-light px-3 transition-colors duration-300 hover:border-night disabled:pointer-events-none disabled:opacity-40';
</script>

<template>
    <nav v-if="meta.lastPage > 1" :aria-label="t('inner.pager.label')" class="mt-10 flex flex-wrap items-center justify-center gap-2">
        <button type="button" :class="btn" :disabled="meta.page <= 1" :aria-label="t('inner.pager.previous')" @click="emit('go', meta.page - 1)"><Icon name="arrow-left" :size="16" /></button>
        <template v-for="(p, i) in pages" :key="p">
            <span v-if="i && p - pages[i - 1] > 1" class="px-1 text-ink-2" aria-hidden="true">…</span>
            <button type="button" :class="[btn, p === meta.page && 'border-night bg-night text-paper']" :aria-current="p === meta.page ? 'page' : undefined" @click="emit('go', p)">{{ p }}</button>
        </template>
        <button type="button" :class="btn" :disabled="meta.page >= meta.lastPage" :aria-label="t('inner.pager.next')" @click="emit('go', meta.page + 1)"><Icon name="arrow" :size="16" /></button>
        <p class="sr-only">{{ t('inner.pager.page', { n: meta.page, total: meta.lastPage }) }}</p>
    </nav>
</template>
