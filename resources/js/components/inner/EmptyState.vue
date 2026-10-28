<script setup lang="ts">
import { useI18n } from 'vue-i18n';

// Honest "not yet" state with the kantha stitch; pages pass specific copy ("Speakers revealing soon").
defineProps<{ title?: string; body?: string; level?: 'h2' | 'h3' }>();
const { t } = useI18n();
</script>

<template>
    <div class="card flex flex-col items-center px-6 py-14 text-center">
        <div class="stitch w-24" aria-hidden="true" />
        <component :is="level ?? 'h2'" class="mt-6 font-display text-3xl font-black uppercase">{{ title ?? t('inner.empty.title') }}</component>
        <p class="mt-3 max-w-md text-ink-2">{{ body ?? t('inner.empty.body') }}</p>
        <div v-if="$slots.default" class="mt-8 flex flex-wrap justify-center gap-3"><slot /></div>
        <div class="stitch mt-10 w-24" aria-hidden="true" />
    </div>
</template>
