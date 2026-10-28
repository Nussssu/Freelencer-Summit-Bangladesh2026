<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Marquee from '@/components/ui/Marquee.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Trust } from '@/types/content';

// Section 2. GITEX one-line ticker (G5) above organiser lockups and partner slots; empty slots are dashed.
const props = defineProps<{ trust: Trust }>();
const { site } = useShared();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
const band = computed(() => [...site.value.organisers.map((o) => o.short), ...props.trust.groups.map((g) => g.label)]);
</script>

<template>
    <section ref="root" data-section="2" :aria-label="t('sections.trust')" class="on-light flow relative z-[1]">
        <div class="flex items-center border-b border-line-light bg-paper-2 text-night">
            <p class="t-label shrink-0 bg-green px-4 py-3 text-snow">{{ t('sections.tickerLabel') }}</p>
            <Marquee :items="trust.ticker" :duration="48" separator="|" class="py-3 text-sm font-medium" />
        </div>

        <div class="container-site grid grid-cols-1 gap-10 py-16 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-4">
                <h2 class="t-label text-ink-2">{{ t('sections.jointly') }}</h2>
                <ul class="mt-4 grid grid-cols-2 gap-px border border-line-light bg-line-light">
                    <li v-for="o in site.organisers" :key="o.short" class="flex min-h-36 flex-col justify-between bg-snow p-4" data-reveal="fade">
                        <img v-if="o.logo" :src="o.logo" :alt="o.name" class="h-12 w-auto" />
                        <span v-else class="font-display text-5xl font-black uppercase" :class="o.short === 'BACCO' && 'text-bacco'">{{ o.short }}</span>
                        <span class="text-xs leading-snug text-ink-2">{{ o.name }}</span>
                    </li>
                </ul>
            </div>
            <div class="grid min-w-0 gap-8 sm:grid-cols-3 lg:col-span-8">
                <div v-for="g in trust.groups" :key="g.label">
                    <h3 class="t-label text-ink-2">{{ g.label }}</h3>
                    <ul class="mt-4 grid gap-2">
                        <li v-for="p in g.partners" :key="p.name" class="card card-hover grid min-h-[88px] place-items-center p-4">
                            <img v-if="p.logo" :src="p.logo" :alt="p.name" class="max-h-12 w-auto" />
                            <span v-else class="font-bold">{{ p.name }}</span>
                        </li>
                        <li v-for="n in Math.max(0, g.slots - g.partners.length)" :key="`slot-${n}`" class="slot t-label" data-reveal="fade">{{ t('common.yourLogo') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-line-light py-4 font-display text-3xl font-black text-ink-2/60 uppercase" :aria-label="t('sections.bandLabel')">
            <Marquee :items="band" :duration="60" reverse separator="/" />
        </div>
    </section>
</template>
