<script setup lang="ts">
import { ref } from 'vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { SectionHeading } from '@/types/inner';

// The About page's closing band (deep teal, rounded, display heading with the last word in gold), laid out like the
// winners mock-up: heading, a rule, one line of text, then the gold Nominate action on the right.
defineProps<{ heading: SectionHeading }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="px-4 pb-16 md:px-6 md:pb-24" aria-labelledby="aw-cta">
        <div class="band relative mx-auto max-w-[90rem] overflow-hidden rounded-2xl px-6 py-12 text-paper md:px-12 md:py-14">
            <div class="relative grid items-center gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-5">
                    <SplitHeading
                        id="aw-cta"
                        :text="heading.title"
                        :emphasis="heading.emphasis ?? undefined"
                        emphasis-class="text-gold"
                        class="max-w-[16ch] font-display text-[clamp(2.5rem,4.6vw,3.75rem)] leading-[0.95] font-black uppercase"
                    />
                </div>
                <p v-if="heading.intro" class="text-lg leading-snug text-paper/90 md:text-xl lg:col-span-4 lg:border-l lg:border-white/25 lg:pl-10" data-reveal="fade">{{ heading.intro }}</p>
                <div class="flex lg:col-span-3 lg:justify-end" data-reveal="fade">
                    <CtaLink href="/awards/nominate" class="!px-7 !py-4">Nominate for Awards</CtaLink>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.band {
    background:
        radial-gradient(60% 120% at 100% 0%, rgb(8 127 140 / 0.55), transparent 70%),
        linear-gradient(120deg, #06454c, #0a5f69);
}
</style>
