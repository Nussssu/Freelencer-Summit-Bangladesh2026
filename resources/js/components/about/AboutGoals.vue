<script setup lang="ts">
import { ref } from 'vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, SectionHeading } from '@/types/inner';
import AboutIcon from './AboutIcon.vue';

// Four key objectives as white cards in a 2 x 2 grid, as in the client's mock-up: icon in a soft teal tile, a thin
// divider, then a bold title over one line of text. Centred heading, no rule under it.
defineProps<{ heading: SectionHeading; goals: AboutContent['goals'] }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="container-site" aria-labelledby="ab-goals">
        <div class="py-12 md:py-14">
            <SplitHeading
                id="ab-goals"
                :text="heading.title"
                :emphasis="heading.emphasis ?? undefined"
                emphasis-class="text-green"
                class="text-center text-[clamp(1.75rem,2.8vw,2.5rem)] leading-tight font-extrabold tracking-[-0.02em] text-night"
            />
            <ul class="mt-10 grid gap-5 md:mt-12 md:grid-cols-2 lg:gap-6">
                <li
                    v-for="g in goals"
                    :key="g.title"
                    class="group flex items-center gap-5 rounded-3xl bg-snow p-6 shadow-[0_18px_40px_-28px_rgb(7_20_17/0.35)] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1.5 hover:shadow-[0_26px_48px_-26px_rgb(8_58_64/0.45)] md:gap-7 md:px-8 md:py-9"
                    data-reveal="fade"
                >
                    <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-[linear-gradient(160deg,rgb(8_127_140/0.14),rgb(8_127_140/0.05))] text-green shadow-[0_10px_22px_-14px_rgb(8_58_64/0.55)] transition-[background-color,color,transform] duration-500 group-hover:scale-105 group-hover:bg-green group-hover:text-snow md:size-20">
                        <AboutIcon :name="g.icon" class="size-8 md:size-9" />
                    </span>
                    <span class="h-16 w-px shrink-0 bg-night/10" aria-hidden="true" />
                    <div class="min-w-0">
                        <h3 class="text-lg leading-snug font-bold text-night md:text-2xl">{{ g.title }}</h3>
                        <p class="mt-1.5 text-ink-2 md:text-[1.0625rem]">{{ g.text }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
