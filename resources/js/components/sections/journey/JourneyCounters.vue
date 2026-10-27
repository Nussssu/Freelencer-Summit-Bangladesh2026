<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import type { Counter } from '@/types/content';

// Four CMS-managed counters (config/summit.php → counters), odometer count-up from 0+ (GITEX G7), accent "+" (Take Off T7).
defineProps<{ counters: Counter[] }>();
const { t } = useI18n();
</script>

<template>
    <div class="container-site pt-[var(--section-y)]">
        <div class="flex flex-wrap items-baseline justify-between gap-4 border-b border-line-dark pb-4">
            <h3 class="t-label text-mint">{{ t('sections.counters') }}</h3>
            <p class="text-sm text-muted">{{ t('sections.countersNote') }}</p>
        </div>
        <dl class="grid grid-cols-2 lg:grid-cols-4">
            <div v-for="c in counters" :key="c.id" class="flex flex-col-reverse justify-end border-b border-line-dark py-8 pr-4 odd:border-r lg:border-r lg:last:border-r-0 [&:not(:first-child)]:pl-4 lg:[&:not(:first-child)]:pl-6">
                <dt class="mt-3 text-sm text-paper/80">
                    {{ c.label }}<span v-if="c.of" class="text-muted"> / {{ c.of }}</span>
                </dt>
                <dd class="t-stat text-[clamp(3rem,6vw,5.5rem)]"><RollingNumber :value="c.value" suffix="+" /></dd>
            </div>
        </dl>
    </div>
</template>
