<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';

// Footer of a multi-step form card: Back (after the first step), Save as draft, and the gold submit (Next or Submit).
defineProps<{ back: boolean; label: string; processing?: boolean; saved?: string }>();
defineEmits<{ back: []; save: [] }>();
const { t } = useI18n();
</script>

<template>
    <p v-if="saved" class="mt-6 text-sm text-ink-2 sm:text-right" role="status">{{ saved }}</p>
    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end sm:gap-4">
        <button v-if="back" type="button" class="inline-flex min-h-12 items-center justify-center gap-2 px-2 font-semibold text-night transition-colors hover:text-green sm:mr-auto" @click="$emit('back')">
            <Icon name="arrow-left" :size="18" /> {{ t('inner.form.back') }}
        </button>
        <button type="button" class="inline-flex min-h-13 items-center justify-center gap-3 rounded-xl border border-night/25 bg-white px-7 font-semibold text-night transition-colors hover:border-green hover:text-green" @click="$emit('save')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><path d="M6 3.5h12v17l-6-4-6 4z" /></svg>
            Save as Draft
        </button>
        <button
            type="submit"
            class="inline-flex min-h-13 items-center justify-center gap-3 rounded-xl bg-gradient-to-b from-[#f2bd3e] to-[#e3a92e] px-8 text-lg font-bold text-night shadow-[0_14px_30px_-16px_rgb(180_120_20/0.7)] transition-[filter,transform] duration-300 hover:brightness-105 active:translate-y-px disabled:opacity-70"
            :disabled="processing"
        >
            {{ processing ? t('inner.form.sending') : label }}
            <svg width="18" height="18" viewBox="0 0 14 14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11 11 3M5 3h6v6" /></svg>
        </button>
    </div>
</template>
