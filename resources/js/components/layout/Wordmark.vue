<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useShared } from '@/composables/useShared';

// Placeholder wordmark until the final logo arrives (brief §21).
const { summit } = useShared();
const { t } = useI18n();
</script>

<template>
    <Link href="/" class="group flex items-center gap-3 leading-none" :aria-label="`${t('brand.title')} ${summit.edition}, home`">
        <svg width="34" height="34" viewBox="0 0 34 34" aria-hidden="true" class="shrink-0">
            <path d="M2 6c8 0 10 6 15 11M32 6c-8 0-10 6-15 11M17 17v15" fill="none" stroke="var(--mint)" stroke-width="2" />
            <circle cx="17" cy="17" r="4" fill="var(--red)" />
        </svg>
        <span class="font-display text-[1.05rem] font-black tracking-wide uppercase">
            <span class="block">Freelancer Summit</span>
            <span class="block text-gold">Bangladesh {{ summit.edition }}</span>
        </span>
    </Link>
</template>
