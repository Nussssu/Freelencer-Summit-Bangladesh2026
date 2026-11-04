<script setup lang="ts">
import { computed, ref } from 'vue';
import InBtn from '@/components/inner/InBtn.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { EASE, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage } from '@/types/inner';

// Exhibition hero shared by the Exhibition page and the exhibitor list: hall photo under a night fade from the left,
// the title with its emphasis in gold. With `action`, one button under it: the page's gold hero action.
const props = defineProps<{ page: InnerPage; action?: boolean }>();
const primary = computed(() => props.page.hero.actions?.find((a) => a.tone === 'gold') ?? null);
const root = ref<HTMLElement>();
useReveal(root);
useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-hero-img]', { scale: 1.08 }, { scale: 1, duration: 2.4, ease: EASE });
});
</script>

<template>
    <section ref="root" class="relative isolate overflow-hidden bg-night text-paper" data-header="light" aria-labelledby="ex-title">
        <div v-if="page.hero.image" class="absolute inset-0 -z-10" aria-hidden="true">
            <img data-hero-img :src="page.hero.image" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover" />
        </div>
        <div class="ex-hero-fade absolute inset-0 -z-10" aria-hidden="true" />
        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero. -->
        <div class="container-site flex min-h-[32rem] flex-col justify-center pt-[calc(var(--header-h)+3.5rem)] pb-16 md:min-h-[38rem] md:pb-24 xl:pt-[calc(153px+3.5rem)]">
            <SplitHeading
                id="ex-title"
                as="h1"
                :text="page.hero.title"
                :emphasis="page.hero.emphasis"
                emphasis-class="text-gold"
                class="max-w-[16ch] font-display text-[clamp(2.75rem,6.4vw,5.5rem)] leading-[0.92] font-black tracking-[-0.01em] [text-wrap:balance] uppercase"
            />
            <div v-if="action && primary" class="mt-9" data-reveal="fade"><InBtn :href="primary.href" tone="gold">{{ primary.label }}</InBtn></div>
        </div>
    </section>
</template>

<style scoped>
.ex-hero-fade {
    background:
        linear-gradient(90deg, rgb(7 20 17 / 0.92) 0%, rgb(7 20 17 / 0.72) 45%, rgb(7 20 17 / 0.3) 100%),
        linear-gradient(0deg, rgb(6 69 76 / 0.55), transparent 60%);
}
</style>
