<script setup lang="ts">
import { computed, ref } from 'vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { InnerPage, SectionHeading } from '@/types/inner';

// Deep teal rounded band: two-line display heading with the last word in gold, Register (gold) and the page's
// secondary hero action (outline) on the right; the default slot replaces those buttons.
const props = defineProps<{ heading: SectionHeading; page: InnerPage }>();
const { site } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const register = computed(() => site.value.ctas.primary.find((c) => c.id === 'register') ?? site.value.ctas.primary[0]);
const secondary = computed(() => props.page.hero.actions?.find((a) => a.tone !== 'gold'));
</script>

<template>
    <section ref="root" class="px-4 pt-12 pb-16 md:px-6 md:pt-14 md:pb-24" aria-labelledby="ab-cta">
        <div class="band relative mx-auto max-w-[90rem] overflow-hidden rounded-2xl px-6 py-12 text-paper md:px-12 md:py-14">
            <div class="relative grid items-center gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <SplitHeading
                        id="ab-cta"
                        :text="heading.title"
                        :emphasis="heading.emphasis ?? undefined"
                        emphasis-class="text-gold"
                        class="max-w-[16ch] font-display text-[clamp(2.5rem,4.6vw,3.75rem)] leading-[0.95] font-black uppercase"
                    />
                </div>
                <div class="flex flex-wrap gap-4 lg:col-span-5 lg:justify-end" data-reveal="fade">
                    <slot>
                        <CtaLink :href="register.href" class="!px-7 !py-4">{{ register.label }}</CtaLink>
                        <CtaLink v-if="secondary" :href="secondary.href" tone="outline" class="!px-7 !py-4">{{ secondary.label }}</CtaLink>
                    </slot>
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
