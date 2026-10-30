<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import type { Counter } from '@/types/content';

// Four CMS-managed counters (config/summit.php → counters), odometer count-up from 0+ (GITEX G7), accent "+" (Take Off T7).
defineProps<{ counters: Counter[] }>();
const { t } = useI18n();
</script>

<template>
    <div class="container-site pt-10 md:pt-12">
        <div class="border-b border-line-light pb-4">
            <h3 class="t-label text-green">{{ t('sections.counters') }}</h3>
        </div>
        <dl class="grid grid-cols-2 lg:grid-cols-4">
            <div v-for="c in counters" :key="c.id" class="flex flex-col-reverse justify-end border-b border-line-light py-8 pr-4 odd:border-r lg:border-r lg:last:border-r-0 [&:not(:first-child)]:pl-4 lg:[&:not(:first-child)]:pl-6">
                <dt class="mt-3 text-sm text-ink-2">
                    {{ c.label }}<span v-if="c.of" class="text-ink-2/70"> / {{ c.of }}</span>
                </dt>
                <dd class="t-stat"><RollingNumber :value="c.value" suffix="+" :spins="2" :duration="2.8" /></dd>
            </div>
        </dl>
    </div>
</template>
