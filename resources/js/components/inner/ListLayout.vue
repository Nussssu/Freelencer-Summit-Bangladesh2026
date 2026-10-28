<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { PageMeta } from '@/types/inner';
import EmptyState from './EmptyState.vue';
import Pagination from './Pagination.vue';
import Skeleton from './Skeleton.vue';

/**
 * Generic list page body: filter toolbar, result count, grid (or rows), skeletons while loading,
 * an honest empty state (nothing published yet) vs "no matches" (filters too narrow), pagination.
 */
withDefaults(
    defineProps<{ meta?: PageMeta; count: number; loading?: boolean; filtered?: boolean; emptyTitle?: string; emptyBody?: string; layout?: 'grid' | 'rows'; columns?: string }>(),
    { loading: false, filtered: false, layout: 'grid', columns: 'sm:grid-cols-2 lg:grid-cols-3' },
);
const emit = defineEmits<{ page: [n: number]; clear: [] }>();
const { t } = useI18n();
</script>

<template>
    <section class="on-light section">
        <div class="container-site">
            <div v-if="$slots.toolbar" class="flex flex-col gap-5 border-b border-line-light pb-6 lg:flex-row lg:items-end"><slot name="toolbar" /></div>
            <div class="mt-6 flex min-h-6 items-center justify-between gap-4" aria-live="polite">
                <p class="t-label text-ink-2">{{ loading ? t('inner.list.loading') : t('inner.list.results', meta?.total ?? count) }}</p>
                <button v-if="filtered" type="button" class="t-label link-u text-green" @click="emit('clear')">{{ t('inner.list.clear') }}</button>
            </div>
            <div v-if="loading" class="mt-6 grid gap-6" :class="layout === 'grid' ? columns : ''"><Skeleton :count="layout === 'grid' ? 6 : 4" :shape="layout === 'grid' ? 'card' : 'row'" /></div>
            <template v-else-if="count">
                <div class="mt-6 grid gap-6" :class="layout === 'grid' ? columns : ''"><slot /></div>
                <Pagination v-if="meta" :meta="meta" @go="emit('page', $event)" />
            </template>
            <EmptyState v-else class="mt-6" :title="filtered ? t('inner.list.noMatch') : emptyTitle" :body="filtered ? undefined : emptyBody">
                <button v-if="filtered" type="button" class="btn btn-outline [clip-path:none]" @click="emit('clear')">{{ t('inner.list.clear') }}</button>
                <slot v-else name="empty" />
            </EmptyState>
        </div>
    </section>
</template>
