<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { Tier } from '@/types/inner';

// Sponsorship package card; benefits come from the database and stay marked provisional until final.
defineProps<{ tier: Tier; rank: number; featured?: boolean }>();
const { t } = useI18n();
</script>

<template>
    <article class="card flex h-full flex-col p-6" :class="featured && 'border-green'">
        <div class="flex items-center justify-between gap-3">
            <span class="t-label text-ink-2">{{ String(rank).padStart(2, '0') }}</span>
            <span v-if="tier.provisional" class="t-label text-ink-2">{{ t('common.provisional') }}</span>
        </div>
        <h3 class="mt-4 font-display text-4xl font-black uppercase" :class="featured && 'text-green'">{{ tier.name }}</h3>
        <ul v-if="tier.benefits.length" class="mt-5 space-y-2 border-t border-line-light pt-5">
            <li v-for="b in tier.benefits" :key="b" class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 bg-green" aria-hidden="true" />{{ b }}</li>
        </ul>
        <div v-if="$slots.default" class="mt-auto pt-6"><slot /></div>
    </article>
</template>
