<script setup lang="ts">
import { useI18n } from 'vue-i18n';

// Slowly rotating circular edition stamp (edition badge idea from Take Off T4, reinterpreted as a seal).
const props = defineProps<{ edition: string; text?: string }>();
const { t } = useI18n();
const id = `stamp-${props.edition}`;
</script>

<template>
    <div class="relative size-32 md:size-40" role="img" :aria-label="`${t('brand.title')} ${edition}`">
        <svg viewBox="0 0 200 200" class="spin-slow absolute inset-0" aria-hidden="true">
            <defs><path :id="id" d="M100 100m-78 0a78 78 0 1 1 156 0a78 78 0 1 1-156 0" /></defs>
            <circle cx="100" cy="100" r="96" fill="none" stroke="var(--gold)" stroke-dasharray="6 5" stroke-width="1.5" />
            <text class="font-mono" font-size="13" letter-spacing="3" fill="currentColor">
                <textPath :href="`#${id}`">{{ (text ?? `${t('brand.title')} · Edition ${edition} · `).toUpperCase() }}</textPath>
            </text>
        </svg>
        <span class="absolute inset-0 grid place-items-center font-display text-4xl font-black text-gold md:text-5xl" aria-hidden="true">'{{ edition.slice(-2) }}</span>
    </div>
</template>
