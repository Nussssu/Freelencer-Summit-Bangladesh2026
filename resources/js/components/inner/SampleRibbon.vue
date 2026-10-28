<script setup lang="ts">
import { useI18n } from 'vue-i18n';

// Marks demo rows (is_sample) so placeholder data never reads as real.
const { t } = useI18n();
</script>

<template>
    <span class="t-label inline-flex bg-gold px-2 py-1 text-[0.625rem] text-night">{{ t('inner.sample') }}</span>
</template>
