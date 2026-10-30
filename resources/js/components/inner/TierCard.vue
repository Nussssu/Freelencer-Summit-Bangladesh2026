<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { Tier } from '@/types/inner';

// Sponsorship package card: tinted, the tier name in Host Grotesk, ticked benefits; marked provisional until final.
withDefaults(defineProps<{ tier: Tier; rank: number; featured?: boolean; tone?: string }>(), { tone: 'tone-teal' });
const { t } = useI18n();
</script>

<template>
    <article class="in-card is-washed in-pad h-full" :class="[tone, featured && 'ring-2 ring-[var(--tint)]']">
        <div class="flex items-center justify-between gap-3">
            <span class="in-num text-3xl!">{{ String(rank).padStart(2, '0') }}</span>
            <span v-if="tier.provisional" class="in-pill">{{ t('common.provisional') }}</span>
        </div>
        <h3 class="in-h3 mt-5 text-2xl!">{{ tier.name }}</h3>
        <ul v-if="tier.benefits.length" class="in-ticks mt-5 border-t border-night/8 pt-5 text-ink-2">
            <li v-for="b in tier.benefits" :key="b">{{ b }}</li>
        </ul>
        <div v-if="$slots.default" class="mt-auto pt-6"><slot /></div>
    </article>
</template>
