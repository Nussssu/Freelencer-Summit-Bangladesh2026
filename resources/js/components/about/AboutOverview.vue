<script setup lang="ts">
import { ref } from 'vue';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, SectionHeading } from '@/types/inner';

// The summit's figures as one counter strip, the same grid as the journey counters elsewhere on the site:
// hairline-divided cells, a large stat over its label. Figures that start with a number roll in like an odometer;
// text figures ("Global") stay as written. The heading is for assistive tech only.
defineProps<{ heading: SectionHeading; overview: AboutContent['overview'] }>();
const root = ref<HTMLElement>();
useReveal(root);

const parse = (v: string) => {
    const m = v.match(/^([\d,]+)(.*)$/);
    return m ? { value: Number(m[1].replace(/,/g, '')), suffix: m[2] } : null;
};
</script>

<template>
    <section ref="root" class="container-site py-12 md:py-14" aria-labelledby="ab-overview">
        <h2 id="ab-overview" class="sr-only">{{ heading.title }}</h2>
        <dl class="grid grid-cols-2 border-b border-line-light lg:grid-cols-4">
            <div
                v-for="s in overview.stats"
                :key="s.label"
                class="flex flex-col-reverse justify-end border-b border-line-light py-5 pr-4 odd:border-r even:pl-4 max-lg:[&:nth-last-child(-n+2)]:border-b-0 md:py-6 lg:border-r lg:border-b-0 lg:last:border-r-0 lg:[&:not(:first-child)]:pl-6"
                data-reveal="fade"
            >
                <dt class="mt-2 text-sm text-ink-2">{{ s.label }}</dt>
                <dd class="text-[clamp(1.75rem,2.6vw,2.25rem)] leading-none font-extrabold tracking-[-0.02em] text-green tabular-nums">
                    <RollingNumber v-if="parse(s.value)" :value="parse(s.value)!.value" :suffix="parse(s.value)!.suffix" :spins="2" :duration="2.8" />
                    <template v-else>{{ s.value }}</template>
                </dd>
            </div>
        </dl>
    </section>
</template>
