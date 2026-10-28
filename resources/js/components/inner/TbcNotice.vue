<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import { useShared } from '@/composables/useShared';

// Shown while config('summit.date_confirmed') is false or no venue is set; disappears once both are confirmed.
const { t } = useI18n();
const { summit } = useShared();
const show = computed(() => !summit.value.dateConfirmed || !summit.value.venue);
</script>

<template>
    <aside v-if="show" class="card flex gap-4 p-5" role="note">
        <Icon name="calendar" class="mt-0.5 shrink-0 text-green" />
        <div>
            <p class="font-semibold">{{ t('inner.tbc.title') }}</p>
            <p class="mt-1 text-ink-2">{{ t('inner.tbc.body', { edition: summit.edition, city: summit.city }) }}</p>
        </div>
    </aside>
</template>
