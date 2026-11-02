<script setup lang="ts">
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, SectionHeading } from '@/types/inner';
import AboutIcon from './AboutIcon.vue';

// Purpose and Vision as two photo cards, as in the client's mock-up: the photo fills the right side and a solid
// white panel with a curved edge (opaque, so the photo never ghosts through the copy) carries the icon, title and line on the left, with a teal swoosh in its corner.
// Stacked on mobile, where the panel sits over a shorter photo.
defineProps<{ heading: SectionHeading; pillars: AboutContent['pillars'] }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="container-site" aria-labelledby="ab-pillars">
        <div class="pt-14 md:pt-20">
            <SplitHeading
                id="ab-pillars"
                :text="heading.title"
                :emphasis="heading.emphasis ?? undefined"
                emphasis-class="text-green"
                class="text-[clamp(2.25rem,4.4vw,3.75rem)] leading-[1.05] font-extrabold tracking-[-0.02em] text-night"
            />
            <ul class="mt-10 grid gap-6 md:mt-12 lg:grid-cols-2 lg:gap-8">
                <li
                    v-for="p in pillars"
                    :key="p.title"
                    class="card group relative isolate min-h-[18rem] overflow-hidden rounded-[1.75rem] bg-snow shadow-[0_28px_56px_-34px_rgb(8_58_64/0.45)] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1.5 hover:shadow-[0_34px_64px_-30px_rgb(8_58_64/0.5)] md:min-h-[20rem]"
                    data-reveal="fade"
                >
                    <Photo :src="p.image" position="center 30%" class="absolute! inset-y-0 right-0 -z-10 w-[62%] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.03] sm:w-[56%]" />

                    <!-- White panel with the curved edge, and the teal swoosh in its lower corner -->
                    <svg class="panel pointer-events-none absolute inset-y-0 left-0 -z-[5] h-full w-[72%] sm:w-[62%]" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient :id="`sw-${p.icon}`" x1="0" x2="1" y1="0" y2="0">
                                <stop offset="0" stop-color="#087f8c" />
                                <stop offset="1" stop-color="#087f8c" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="M0 0H100C91 32 80 66 72 100H0Z" fill="#fff" />
                        <path d="M0 84C14 94 32 98 56 100H0Z" :fill="`url(#sw-${p.icon})`" />
                    </svg>

                    <div class="relative flex h-full max-w-[60%] flex-col justify-center gap-4 p-7 sm:max-w-[52%] md:p-9">
                        <span class="grid size-14 place-items-center rounded-2xl bg-[linear-gradient(160deg,rgb(8_127_140/0.14),rgb(8_127_140/0.06))] text-green shadow-[0_10px_22px_-14px_rgb(8_58_64/0.6)] transition-transform duration-500 group-hover:scale-105">
                            <AboutIcon :name="p.icon" class="size-7" />
                        </span>
                        <h3 class="text-[clamp(1.5rem,2.2vw,2rem)] leading-tight font-extrabold tracking-[-0.02em] text-night">{{ p.title }}</h3>
                        <p class="text-base leading-relaxed text-ink-2 md:text-[1.0625rem]">{{ p.text }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
