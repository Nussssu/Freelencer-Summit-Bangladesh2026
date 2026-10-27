<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import { useShared } from '@/composables/useShared';

// Unconfigured channels render as dimmed, non-link icons instead of dead links.
const { summit } = useShared();
const { t } = useI18n();
const names = { facebook: 'Facebook', linkedin: 'LinkedIn', youtube: 'YouTube', x: 'X' } as const;
</script>

<template>
    <ul class="flex items-center gap-1">
        <li v-for="(label, key) in names" :key="key">
            <a v-if="summit.social[key]" :href="summit.social[key]!" target="_blank" rel="noopener" :aria-label="label" class="grid size-11 place-items-center transition-colors duration-300 hover:text-gold">
                <Icon :name="key" :size="16" />
            </a>
            <span v-else class="grid size-11 place-items-center opacity-40" :title="t('header.socialSoon', { name: label })" role="img" :aria-label="t('header.socialSoon', { name: label })">
                <Icon :name="key" :size="16" />
            </span>
        </li>
    </ul>
</template>
