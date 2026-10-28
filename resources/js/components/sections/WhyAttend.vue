<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Audience, Heading } from '@/types/content';

// Section 5. Not a card grid: numbered rows (Take Off 01/02, T6). Desktop: hover/focus swaps a side panel.
// Mobile: each row is an accordion with a plus icon.
defineProps<{ audiences: Audience[]; heading: Heading }>();
const { t } = useI18n();
const active = ref(0);
const openRow = ref<number | null>(0);
const root = ref<HTMLElement>();
useReveal(root);
const num = (i: number) => String(i + 1).padStart(2, '0');
</script>

<template>
    <section ref="root" data-section="5" aria-labelledby="why-title" class="on-light flow section relative z-[1]">
        <div class="container-site">
            <p class="t-label text-green">{{ t('sections.why') }}</p>
            <SplitHeading id="why-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3 max-w-3xl" />

            <div class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-12">
                <ol class="min-w-0 border-t border-line-light md:col-span-7">
                    <li v-for="(a, i) in audiences" :key="a.title" class="border-b border-line-light">
                        <!-- Desktop row -->
                        <button
                            type="button"
                            class="group hidden w-full items-baseline gap-6 py-4 text-left transition-colors duration-300 md:flex"
                            :class="active === i ? 'text-green' : 'hover:text-green'"
                            :aria-pressed="active === i"
                            aria-controls="why-panel"
                            @mouseenter="active = i"
                            @focus="active = i"
                            @click="active = i"
                        >
                            <span class="t-label w-8 text-ink-2">{{ num(i) }}</span>
                            <span class="font-display text-[clamp(2rem,3.4vw,3.25rem)] leading-none font-black uppercase transition-transform duration-300 group-hover:translate-x-2">{{ a.title }}</span>
                            <Icon name="arrow" class="ml-auto self-center opacity-0 transition-opacity duration-300" :class="active === i && 'opacity-100'" />
                        </button>
                        <!-- Mobile accordion -->
                        <h3 class="md:hidden">
                            <button type="button" class="flex min-h-14 w-full items-center gap-4 py-3 text-left" :aria-expanded="openRow === i" :aria-controls="`why-m-${i}`" @click="openRow = openRow === i ? null : i">
                                <span class="t-label w-6 text-ink-2">{{ num(i) }}</span>
                                <span class="font-display text-3xl font-black uppercase">{{ a.title }}</span>
                                <Icon name="plus" class="ml-auto transition-transform duration-300" :class="openRow === i && 'rotate-45'" />
                            </button>
                        </h3>
                        <div v-show="openRow === i" :id="`why-m-${i}`" class="pb-6 pl-10 md:hidden">
                            <ul class="space-y-2">
                                <li v-for="b in a.benefits" :key="b" class="flex gap-3"><span class="mt-2.5 h-px w-4 shrink-0 bg-gold" aria-hidden="true" />{{ b }}</li>
                            </ul>
                            <CtaLink :href="a.href" tone="outline" class="mt-4">{{ a.cta }}</CtaLink>
                        </div>
                    </li>
                </ol>

                <div id="why-panel" aria-live="polite" class="hidden md:col-span-5 md:block">
                    <div class="sticky top-[calc(var(--header-h)+24px)] card p-8">
                        <p class="font-display text-[8rem] leading-[0.8] font-black t-outline" style="--outline: var(--green)" aria-hidden="true">{{ num(active) }}</p>
                        <h3 class="mt-6 font-display text-4xl font-black uppercase">{{ audiences[active].title }}</h3>
                        <ul class="mt-6 space-y-4">
                            <li v-for="b in audiences[active].benefits" :key="b" class="flex gap-3 border-t border-line-light pt-4"><span class="mt-3 h-px w-4 shrink-0 bg-gold" aria-hidden="true" />{{ b }}</li>
                        </ul>
                        <CtaLink :href="audiences[active].href" class="mt-8">{{ audiences[active].cta }}</CtaLink>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
