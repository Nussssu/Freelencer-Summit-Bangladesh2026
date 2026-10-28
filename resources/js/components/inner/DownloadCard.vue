<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import type { DownloadItem } from '@/types/inner';

// Enabled only when the file exists (href set by ContentQuery); otherwise an honest "Available soon".
defineProps<{ item: DownloadItem }>();
const { t } = useI18n();
const mb = (b: number) => (b / 1048576).toFixed(1);
</script>

<template>
    <a v-if="item.href" :href="item.href" download class="card card-hover group flex min-h-24 items-center gap-4 p-5">
        <span class="grid size-11 shrink-0 place-items-center bg-green text-paper"><Icon name="download" :size="18" /></span>
        <span class="min-w-0">
            <span class="block font-semibold">{{ item.title }}</span>
            <span class="t-label text-ink-2">{{ t('inner.download.action') }}<template v-if="item.size"> · {{ t('inner.download.size', { size: mb(item.size) }) }}</template></span>
        </span>
    </a>
    <div v-else class="card flex min-h-24 items-center gap-4 p-5 opacity-70" aria-disabled="true">
        <span class="grid size-11 shrink-0 place-items-center border border-line-light text-ink-2"><Icon name="download" :size="18" /></span>
        <span>
            <span class="block font-semibold">{{ item.title }}</span>
            <span class="t-label text-ink-2">{{ t('inner.download.soon') }}</span>
        </span>
    </div>
</template>
