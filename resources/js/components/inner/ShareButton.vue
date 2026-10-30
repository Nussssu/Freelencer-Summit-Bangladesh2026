<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

// Native share sheet where available, otherwise copies the link and says so.
const props = defineProps<{ title: string; url?: string }>();
const { t } = useI18n();
const copied = ref(false);

async function share() {
    const url = props.url ?? location.href;
    try {
        if (navigator.share) return await navigator.share({ title: props.title, url });
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2500);
    } catch {
        /* dismissed share sheet */
    }
}
</script>

<template>
    <span class="inline-flex items-center">
        <button type="button" class="in-btn is-ghost" @click="share">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 15V3M7 8l5-5 5 5M5 13v7h14v-7" /></svg>
            {{ t('inner.share.action') }}
        </button>
        <span class="sr-only" aria-live="polite">{{ copied ? t('inner.share.copied') : '' }}</span>
        <span v-if="copied" class="ml-3 text-sm font-semibold text-green" aria-hidden="true">{{ t('inner.share.copied') }}</span>
    </span>
</template>
