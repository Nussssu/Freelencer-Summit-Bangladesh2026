<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { PageMeta } from '@/types/inner';
import EmptyState from './EmptyState.vue';
import Pagination from './Pagination.vue';
import Skeleton from './Skeleton.vue';

/**
 * Generic list body: filter toolbar on a soft rounded panel, result count, grid (or rows), skeletons while loading,
 * an honest empty state (nothing published yet) vs "no matches" (filters too narrow), pagination.
 */
withDefaults(
    defineProps<{ meta?: PageMeta; count: number; loading?: boolean; filtered?: boolean; emptyTitle?: string; emptyBody?: string; layout?: 'grid' | 'rows'; columns?: string; bare?: boolean }>(),
    { loading: false, filtered: false, layout: 'grid', columns: 'sm:grid-cols-2 lg:grid-cols-3', bare: false },
);
const emit = defineEmits<{ page: [n: number]; clear: [] }>();
const { t } = useI18n();
</script>

<template>
    <component :is="bare ? 'div' : 'section'" :class="!bare && 'in-section'">
        <div :class="!bare && 'container-site'">
            <div v-if="$slots.toolbar" class="in-card flex flex-col gap-5 p-4 md:p-5 lg:flex-row lg:items-end"><slot name="toolbar" /></div>
            <div class="mt-6 flex min-h-6 items-center justify-between gap-4" aria-live="polite">
                <p class="in-meta">{{ loading ? t('inner.list.loading') : t('inner.list.results', meta?.total ?? count) }}</p>
                <button v-if="filtered" type="button" class="in-link min-h-0! text-sm" @click="emit('clear')">{{ t('inner.list.clear') }}</button>
            </div>
            <div v-if="loading" class="mt-5 grid gap-5" :class="layout === 'grid' ? columns : ''"><Skeleton :count="layout === 'grid' ? 6 : 4" :shape="layout === 'grid' ? 'card' : 'row'" /></div>
            <template v-else-if="count">
                <div class="mt-5 grid gap-5" :class="layout === 'grid' ? columns : ''"><slot /></div>
                <Pagination v-if="meta" :meta="meta" @go="emit('page', $event)" />
            </template>
            <EmptyState v-else class="mt-5" :title="filtered ? t('inner.list.noMatch') : emptyTitle" :body="filtered ? undefined : emptyBody" :icon="filtered ? 'target' : 'clock'">
                <button v-if="filtered" type="button" class="in-btn is-ghost" @click="emit('clear')">{{ t('inner.list.clear') }}</button>
                <slot v-else name="empty" />
            </EmptyState>
        </div>
    </component>
</template>
