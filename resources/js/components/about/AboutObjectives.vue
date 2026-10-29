<script setup lang="ts">
import { ref } from 'vue';
import { useReveal } from '@/composables/useReveal';
import type { SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// Nine strategic objectives (brief §3, wording unchanged): sticky heading on the left, editorial rows with large
// numerals and hairline rules on the right; each row fades in as it arrives. Rows are text, so the hover is a calm
// emphasis (numeral fills, rule turns teal), and nothing here takes keyboard focus.
defineProps<{ heading: SectionHeading; objectives: string[] }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="section" aria-labelledby="ab-objectives">
        <div class="container-site grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <!-- shortcut: top offset hard-codes the 2-row header at xl (see AboutHero) -->
                <div class="lg:sticky lg:top-[calc(var(--header-h)+2rem)] xl:top-[calc(153px+2rem)]">
                    <AboutHead id="ab-objectives" :heading="heading" />
                </div>
            </div>
            <ol class="border-t border-night lg:col-span-8">
                <li
                    v-for="(o, i) in objectives"
                    :key="o"
                    class="group grid grid-cols-[4.5rem_1fr] items-baseline gap-4 border-b border-line-light py-7 transition-colors duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:border-green md:grid-cols-[7rem_1fr] md:py-9"
                    data-reveal="fade"
                >
                    <span class="numeral font-display text-5xl leading-none font-black tabular-nums md:text-7xl" aria-hidden="true">{{ String(i + 1).padStart(2, '0') }}</span>
                    <p class="max-w-[48ch] text-lg leading-relaxed md:text-xl">{{ o }}</p>
                </li>
            </ol>
        </div>
    </section>
</template>

<style scoped>
/* Outlined numeral that fills with teal on hover: emphasis from weight of ink, not from motion */
.numeral {
    color: transparent;
    -webkit-text-stroke: 1.5px var(--green);
    transition: color 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.group:hover .numeral {
    color: var(--green);
}
</style>
