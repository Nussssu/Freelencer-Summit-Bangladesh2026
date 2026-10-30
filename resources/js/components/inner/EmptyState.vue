<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Glyph, { type GlyphName } from './Glyph.vue';

// Honest "not yet" state on a soft rounded card; pages pass specific copy ("Speakers revealing soon").
withDefaults(defineProps<{ title?: string; body?: string; level?: 'h2' | 'h3'; icon?: GlyphName; tone?: string }>(), { icon: 'clock', tone: 'tone-teal' });
const { t } = useI18n();
</script>

<template>
    <div class="in-card is-washed flex flex-col items-center px-6 py-12 text-center md:py-16" :class="tone">
        <span class="in-disc w-16!"><Glyph :name="icon" /></span>
        <component :is="level ?? 'h2'" class="in-h3 mt-5 text-2xl!">{{ title ?? t('inner.empty.title') }}</component>
        <p class="mt-3 max-w-md text-ink-2">{{ body ?? t('inner.empty.body') }}</p>
        <div v-if="$slots.default" class="mt-8 flex flex-wrap justify-center gap-3"><slot /></div>
    </div>
</template>
