<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { DownloadItem } from '@/types/inner';
import Glyph from './Glyph.vue';

// Enabled only when the file exists (href set by ContentQuery); otherwise an honest "Available soon".
withDefaults(defineProps<{ item: DownloadItem; tone?: string }>(), { tone: 'tone-teal' });
const { t } = useI18n();
const mb = (b: number) => (b / 1048576).toFixed(1);
</script>

<template>
    <a v-if="item.href" :href="item.href" download class="in-card is-washed is-link flex-row! items-center gap-4 p-5" :class="tone">
        <span class="in-disc w-12!"><Glyph name="download" /></span>
        <span class="min-w-0 flex-1">
            <span class="block font-bold text-night">{{ item.title }}</span>
            <span class="in-meta">{{ t('inner.download.action') }}<template v-if="item.size"> · {{ t('inner.download.size', { size: mb(item.size) }) }}</template></span>
        </span>
    </a>
    <div v-else class="in-card is-washed flex-row! items-center gap-4 p-5" :class="tone" aria-disabled="true">
        <span class="in-disc w-12! opacity-70"><Glyph name="doc" /></span>
        <span class="min-w-0 flex-1">
            <span class="block font-bold text-night">{{ item.title }}</span>
            <span class="in-meta">{{ t('inner.download.soon') }}</span>
        </span>
    </div>
</template>
