<script setup lang="ts">
import { computed, ref } from 'vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SpeakerDialog from '@/components/speakers/SpeakerDialog.vue';
import SpeakerHoverCard from '@/components/speakers/SpeakerHoverCard.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Heading, Speaker } from '@/types/content';

// Section 7. BPO role labels + "Details" (B6), reinterpreted as the speaker popup. Every slot starts as
// "Revealing soon" (blurred silhouette); the data shape is ready for real speakers.
defineProps<{ speakers: Speaker[]; heading: Heading }>();
const { site } = useShared();
const cta = computed(() => site.value.ctas.secondary.find((c) => c.id === 'speakers')!);
const root = ref<HTMLElement>();
useReveal(root);
// Index of the speaker shown in the popup (null = closed).
const selected = ref<number | null>(null);
</script>

<template>
    <section ref="root" data-section="7" aria-labelledby="sp-title" class="on-light flow section relative z-[1]">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <SplitHeading id="sp-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2" />
                </div>
                <CtaLink :href="cta.href" tone="ghost">{{ cta.label }}</CtaLink>
            </div>

            <!-- One grid for everyone (chief guest included). Card shows the photo; hover/focus flips it to a profile panel and a
                 click opens the speaker popup (no profile pages). -->
            <ul class="mt-12 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <!-- Two rows only: 4 cards on phones/tablets (2 columns), 8 on desktop (4 columns) -->
                <li v-for="(s, i) in speakers.slice(0, 8)" :key="s.slug" :class="i >= 4 && 'hidden lg:block'" data-reveal="fade">
                    <SpeakerHoverCard :person="s" @click="selected = i" />
                </li>
            </ul>
        </div>

        <SpeakerDialog v-model:index="selected" :people="speakers" />
    </section>
</template>

