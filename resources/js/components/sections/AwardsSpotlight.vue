<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Awards } from '@/types/content';

// Section 9. Own identity (deep green + gold), clearly separate from summit registration (brief §9).
defineProps<{ awards: Awards }>();
const { t } = useI18n();
const { summit, site } = useShared();
const nominate = computed(() => site.value.ctas.primary.find((c) => c.id === 'nominate')!);
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" data-section="9" aria-labelledby="aw-title" class="on-light light-tokens soft-green section relative z-[1] overflow-hidden">
        <div class="container-site grid grid-cols-1 gap-12 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-7">
                <p class="t-label text-gold">{{ t('sections.awards') }} {{ summit.edition }}</p>
                <SplitHeading id="aw-title" :text="awards.headline" :emphasis="awards.emphasis" emphasis-class="text-gold" class="t-h2-xl mt-4" />
                <p class="mt-8 max-w-xl text-lg">{{ awards.eligibility }}</p>
                <p class="t-label mt-4">{{ t('sections.deadline') }}: <span class="text-gold">{{ awards.deadline ?? t('common.tba') }}</span></p>

                <!-- Application status as a stitched line (brief §9 statuses) -->
                <div class="mt-10">
                    <h3 class="t-label text-ink-2">{{ t('sections.status') }}</h3>
                    <ol class="relative mt-4 grid grid-cols-4">
                        <span class="stitch absolute inset-x-[12.5%] top-[7px]" data-reveal="stitch" aria-hidden="true" />
                        <li v-for="(st, i) in awards.statuses" :key="st" class="relative flex flex-col items-center gap-3 text-center">
                            <span class="size-4 border-2 border-gold" :class="i === awards.statuses.length - 1 ? 'bg-gold' : 'bg-snow'" aria-hidden="true" />
                            <span class="t-label text-[0.6875rem]">{{ st }}</span>
                        </li>
                    </ol>
                </div>

                <div class="mt-10 flex flex-wrap gap-2">
                    <CtaLink :href="nominate.href">{{ nominate.label }}</CtaLink>
                    <CtaLink href="/awards/categories" tone="outline">{{ t('sections.categories') }}</CtaLink>
                </div>
            </div>

            <div class="relative flex min-h-96 flex-col items-center justify-center lg:col-span-5">
                <svg viewBox="0 0 200 200" class="spin-slow absolute size-72 opacity-80 md:size-96" aria-hidden="true">
                    <defs><path id="aw-ring" d="M100 100m-82 0a82 82 0 1 1 164 0a82 82 0 1 1-164 0" /></defs>
                    <text font-size="12" letter-spacing="4" fill="var(--gold-ink)" class="font-mono"><textPath href="#aw-ring">{{ awards.badge.toUpperCase() }}</textPath></text>
                </svg>
                <ul class="fan relative z-10 w-64" :aria-label="t('sections.categories')">
                    <li v-for="(c, i) in awards.categories" :key="c" :style="{ '--i': i - (awards.categories.length - 1) / 2 }">
                        <Link href="/awards/categories" class="block border border-night/30 bg-paper px-4 py-3 font-display text-xl font-black text-night uppercase">{{ c }}</Link>
                    </li>
                </ul>
                <p v-if="awards.categoriesProvisional" class="t-label relative z-10 mt-6 text-ink-2">{{ t('common.provisional') }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.fan {
    display: grid;
}
.fan li {
    grid-area: 1 / 1;
    transform: rotate(calc(var(--i) * 3deg)) translateY(calc(var(--i) * 6px));
    transition: transform 0.6s var(--ease-out);
}
.fan:hover li,
.fan:focus-within li {
    transform: rotate(calc(var(--i) * 9deg)) translate(calc(var(--i) * 18px), calc(var(--i) * 44px));
}
@media (max-width: 767px), (prefers-reduced-motion: reduce) {
    .fan {
        gap: 8px;
    }
    .fan li,
    .fan:hover li {
        grid-area: auto;
        transform: none;
    }
}
</style>
