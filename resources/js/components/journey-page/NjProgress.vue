<script setup lang="ts">
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import Icon from '@/components/ui/Icon.vue';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import { useReveal } from '@/composables/useReveal';
import type { Counter } from '@/types/content';
import type { JourneyContent, SectionHeading } from '@/types/inner';

// "The journey so far": odometer counters with a bronze meter against each target, then three recent stops as rounded photo cards
// (place, then the date behind a divider).
defineProps<{ heading: SectionHeading; counters: Counter[]; reel: JourneyContent['reel'] }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="section" aria-labelledby="nj-progress">
        <div class="container-site">
            <SectionHead id="nj-progress" :heading="heading" />

            <dl class="mt-14 grid grid-cols-2 gap-y-10 lg:grid-cols-4">
                <div v-for="c in counters" :key="c.id" class="flex flex-col border-l border-line-light px-5 first:border-l-0 first:pl-0 lg:px-8 [&:nth-child(3)]:border-l-0 [&:nth-child(3)]:pl-0 lg:[&:nth-child(3)]:border-l lg:[&:nth-child(3)]:pl-8" data-reveal="fade">
                    <dt class="order-2 mt-3 text-sm text-ink-2">
                        {{ c.label }}<span v-if="c.of" class="text-ink-2/70"> of {{ c.of }}</span>
                    </dt>
                    <dd class="order-1">
                        <span class="block text-[clamp(1.75rem,2.6vw,2.25rem)] leading-none font-extrabold tracking-[-0.02em] tabular-nums"><RollingNumber :value="c.value" :suffix="c.of ? '' : '+'" /></span>
                        <!-- Meter against the campaign target; participants have no ceiling, so it shows a full bar -->
                        <span class="mt-5 block h-[3px] bg-line-light" aria-hidden="true">
                            <span class="block h-full bg-gradient-to-r from-green to-gold" :style="{ width: c.of ? `${Math.min(100, (c.value / c.of) * 100)}%` : '100%' }" data-reveal="stitch" />
                        </span>
                    </dd>
                </div>
            </dl>

            <ul class="mt-20 grid gap-5 md:grid-cols-3">
                <li v-for="(r, i) in reel" :key="r.place" class="group" :class="i === 1 && 'md:translate-y-10'">
                    <figure class="m-0 overflow-hidden rounded-2xl border border-white/80 bg-paper shadow-[0_18px_40px_-28px_rgb(8_58_64/0.35)] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:-translate-y-1.5 group-hover:shadow-[0_28px_50px_-26px_rgb(8_58_64/0.45)]">
                        <div class="overflow-hidden" data-reveal="clip">
                            <Photo :src="r.image" :alt="r.place" class="aspect-[4/3] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.04]" />
                        </div>
                        <figcaption class="flex items-center justify-between gap-4 px-5 py-4 md:px-6 md:py-5" data-reveal="fade">
                            <span class="text-lg leading-snug font-bold">{{ r.place }}</span>
                            <span class="flex shrink-0 items-center gap-2 border-l border-line-light pl-4 text-sm text-ink-2 md:pl-6"><Icon name="calendar" :size="18" class="text-night" /><time>{{ r.date }}</time></span>
                        </figcaption>
                    </figure>
                </li>
            </ul>
        </div>
    </section>
</template>
