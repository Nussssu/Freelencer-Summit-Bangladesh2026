<script setup lang="ts">
import { ref } from 'vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// Organisers as two substantial panels: large logo field (wordmark until logos are supplied), role, full name.
defineProps<{ heading: SectionHeading }>();
const { site } = useShared();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="section" aria-labelledby="ab-organisers">
        <div class="container-site">
            <AboutHead id="ab-organisers" :heading="heading" />
            <ul class="mt-14 grid gap-6 md:grid-cols-2">
                <li
                    v-for="o in site.organisers"
                    :key="o.short"
                    class="group flex flex-col bg-snow shadow-[0_24px_48px_-28px_rgb(8_58_64/0.35)] transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1"
                    data-reveal="fade"
                >
                    <div class="grid h-48 place-items-center border-b border-line-light bg-paper md:h-56">
                        <img v-if="o.logo" :src="o.logo" :alt="o.name" class="max-h-28 w-auto" loading="lazy" />
                        <span v-else class="font-display text-7xl font-black tracking-[-0.01em] text-bacco transition-colors duration-500 group-hover:text-green md:text-8xl" aria-hidden="true" translate="no">{{ o.short }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-6 p-6 md:p-8">
                        <div>
                            <p class="text-sm font-semibold text-green">{{ o.role }}</p>
                            <h3 class="mt-2 text-xl leading-snug font-semibold md:text-2xl">{{ o.name }}</h3>
                        </div>
                        <span class="t-label shrink-0 border border-line-light px-2 py-1 text-ink-2" translate="no">{{ o.short }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
