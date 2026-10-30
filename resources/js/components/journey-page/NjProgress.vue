<script setup lang="ts">
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import { useReveal } from '@/composables/useReveal';
import type { Counter } from '@/types/content';
import type { JourneyContent, SectionHeading } from '@/types/inner';

// "The journey so far": odometer counters with a bronze meter against each target, then three recent stops as photo proof.
defineProps<{ heading: SectionHeading; counters: Counter[]; reel: JourneyContent['reel'] }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="section bg-snow" aria-labelledby="nj-progress">
        <div class="container-site">
            <SectionHead id="nj-progress" :heading="heading" />

            <dl class="mt-14 grid grid-cols-2 gap-y-10 lg:grid-cols-4">
                <div v-for="c in counters" :key="c.id" class="flex flex-col border-l border-line-light px-5 first:border-l-0 first:pl-0 lg:px-8 [&:nth-child(3)]:border-l-0 [&:nth-child(3)]:pl-0 lg:[&:nth-child(3)]:border-l lg:[&:nth-child(3)]:pl-8" data-reveal="fade">
                    <dt class="order-2 mt-3 text-sm text-ink-2">
                        {{ c.label }}<span v-if="c.of" class="text-ink-2/70"> of {{ c.of }}</span>
                    </dt>
                    <dd class="order-1">
                        <span class="t-stat block text-[clamp(2.25rem,5vw,4.75rem)]"><RollingNumber :value="c.value" :suffix="c.of ? '' : '+'" /></span>
                        <!-- Meter against the campaign target; participants have no ceiling, so it shows a full bar -->
                        <span class="mt-5 block h-[3px] bg-line-light" aria-hidden="true">
                            <span class="block h-full bg-gradient-to-r from-green to-gold" :style="{ width: c.of ? `${Math.min(100, (c.value / c.of) * 100)}%` : '100%' }" data-reveal="stitch" />
                        </span>
                    </dd>
                </div>
            </dl>

            <ul class="mt-20 grid gap-5 md:grid-cols-3">
                <li v-for="(r, i) in reel" :key="r.place" class="group" :class="i === 1 && 'md:translate-y-10'">
                    <figure class="m-0">
                        <div class="overflow-hidden" data-reveal="clip">
                            <Photo :src="r.image" :alt="r.place" class="aspect-[4/3] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.04]" />
                        </div>
                        <figcaption class="mt-4 flex items-baseline justify-between gap-4 border-t border-line-light pt-3" data-reveal="fade">
                            <span class="font-semibold">{{ r.place }}</span>
                            <time class="shrink-0 text-sm text-ink-2">{{ r.date }}</time>
                        </figcaption>
                    </figure>
                </li>
            </ul>
        </div>
    </section>
</template>
