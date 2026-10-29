<script setup lang="ts">
import { computed, ref } from 'vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useMagnetic } from '@/composables/useMagnetic';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { SectionHeading } from '@/types/inner';

// A calmer echo of the landing's final CTA: big green line + stitch, one magnetic primary (Register), the other four
// journeys grouped beneath as quiet outline buttons. Soft-green ground like the kit's CtaBand.
defineProps<{ heading: SectionHeading }>();
const { site } = useShared();
const root = ref<HTMLElement>();
const primaryWrap = ref<HTMLElement>();
useReveal(root);
useMagnetic(primaryWrap);

const primary = computed(() => site.value.ctas.primary.find((c) => c.id === 'register') ?? site.value.ctas.primary[0]);
const others = computed(() => site.value.ctas.primary.filter((c) => c.id !== primary.value.id));
</script>

<template>
    <section ref="root" class="soft-green on-light light-tokens relative overflow-hidden py-24 md:py-32" aria-labelledby="ab-cta">
        <div class="container-site">
            <SplitHeading
                id="ab-cta"
                :text="heading.title"
                :emphasis="heading.emphasis ?? undefined"
                emphasis-class="text-night"
                class="max-w-5xl font-display text-[clamp(3rem,8vw,7rem)] leading-[0.88] font-black text-green uppercase [text-wrap:balance]"
            />
            <div class="stitch mt-8 h-2 max-w-md" data-reveal="stitch" aria-hidden="true" />
            <p v-if="heading.intro" class="mt-8 max-w-[56ch] text-lg leading-relaxed" data-reveal="fade">{{ heading.intro }}</p>

            <div class="mt-12" data-reveal="fade">
                <span ref="primaryWrap" class="inline-block">
                    <CtaLink :href="primary.href" class="!px-8 !py-5 text-base">{{ primary.label }}</CtaLink>
                </span>
            </div>
            <ul class="mt-8 flex flex-col gap-2 border-t border-night/15 pt-8 sm:flex-row sm:flex-wrap" data-reveal="fade">
                <li v-for="c in others" :key="c.id"><CtaLink :href="c.href" tone="outline" class="w-full sm:w-auto">{{ c.label }}</CtaLink></li>
            </ul>
        </div>
    </section>
</template>
