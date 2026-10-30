<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useShared } from '@/composables/useShared';
import Glyph from './Glyph.vue';

// Shown while config('summit.date_confirmed') is false or no venue is set; disappears once both are confirmed.
const { t } = useI18n();
const { summit } = useShared();
const show = computed(() => !summit.value.dateConfirmed || !summit.value.venue);
</script>

<template>
    <aside v-if="show" class="in-card tone-sun is-washed flex-row! gap-4 p-5" role="note">
        <span class="in-disc w-11!"><Glyph name="calendar" /></span>
        <div>
            <p class="font-bold text-night">{{ t('inner.tbc.title') }}</p>
            <p class="mt-1 text-ink-2">{{ t('inner.tbc.body', { edition: summit.edition, city: summit.city }) }}</p>
        </div>
    </aside>
</template>
