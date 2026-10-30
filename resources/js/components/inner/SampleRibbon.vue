<script setup lang="ts">
import { useI18n } from 'vue-i18n';

// Marks demo rows (is_sample) so placeholder data never reads as real.
const { t } = useI18n();
</script>

<template>
    <span class="inline-flex rounded-full bg-gold px-2.5 py-0.5 text-xs font-semibold text-night">{{ t('inner.sample') }}</span>
</template>
