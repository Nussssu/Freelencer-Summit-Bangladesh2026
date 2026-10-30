<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { Option } from '@/types/inner';

// Single-choice chip row ("All" + options), same look as the landing agenda filters. '' means All.
defineProps<{ options: Option[]; label: string; allLabel?: string }>();
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
</script>

<template>
    <div role="group" :aria-label="label">
        <ul class="flex flex-wrap gap-2">
            <li v-for="o in [{ value: '', label: allLabel ?? t('inner.list.all') }, ...options]" :key="o.value">
                <button type="button" class="in-chip" :aria-pressed="model === o.value" @click="model = o.value">{{ o.label }}</button>
            </li>
        </ul>
    </div>
</template>
