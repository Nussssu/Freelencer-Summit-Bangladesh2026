<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import { useShared } from '@/composables/useShared';

const { site } = useShared();
const { t } = useI18n();
const nominate = computed(() => site.value.ctas.primary.find((c) => c.id === 'nominate')!);
</script>

<template>
    <div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 gap-2 on-light border-t border-line-light bg-paper/95 p-2 md:hidden">
        <CtaLink href="/register" class="!px-3 !text-xs">{{ t('cta.register') }}</CtaLink>
        <CtaLink :href="nominate.href" tone="outline" class="!px-3 !text-xs">{{ nominate.label }}</CtaLink>
    </div>
</template>
