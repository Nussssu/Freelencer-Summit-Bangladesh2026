<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import { gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { About } from '@/types/content';
import type { SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// "More than an event": the landing's editorial statement (scroll-scrubbed gold marker, rotated stamp), then a calm
// count-up of the confirmed scale from config (divisions, districts, campuses). No other numbers.
const props = defineProps<{ heading: SectionHeading; about: About }>();
const { t } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const parts = computed(() => {
    const i = props.about.statement.indexOf(props.about.highlight);
    return i < 0 ? [props.about.statement, '', ''] : [props.about.statement.slice(0, i), props.about.highlight, props.about.statement.slice(i + props.about.highlight.length)];
});
const figures = computed(() => [
    { value: summit.value.divisions, label: t('sections.tabs.division') },
    { value: summit.value.districts, label: t('sections.tabs.district') },
    { value: summit.value.campuses, label: t('sections.tabs.campus') },
]);

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-marker]', { backgroundSize: '0% 100%' }, { backgroundSize: '100% 100%', ease: 'none', scrollTrigger: { trigger: '[data-marker]', start: 'top 80%', end: 'bottom 45%', scrub: true } });
});
</script>

<template>
    <section ref="root" class="section relative overflow-hidden bg-snow" aria-labelledby="ab-why">
        <div class="container-site">
            <div class="grid gap-10 lg:grid-cols-12">
                <AboutHead id="ab-why" :heading="heading" class="lg:col-span-4" />
                <div class="lg:col-span-8">
                    <p class="font-display text-[clamp(2.25rem,4.6vw,4.5rem)] leading-[0.98] font-black uppercase [text-wrap:balance]" data-reveal="fade">
                        {{ parts[0] }}<mark data-marker class="marker">{{ parts[1] }}</mark>{{ parts[2] }}
                    </p>
                    <div class="stitch mt-10 max-w-xs" data-reveal="stitch" aria-hidden="true" />
                    <div class="relative mt-10 grid gap-8 md:grid-cols-[1fr_auto]">
                        <div class="max-w-[60ch] space-y-5 text-lg leading-relaxed text-ink-2">
                            <p v-for="p in about.paragraphs" :key="p" data-reveal="fade">{{ p }}</p>
                        </div>
                        <p class="stamp t-label self-start justify-self-start md:mt-2 md:justify-self-end" data-reveal="fade">{{ about.stamp }}</p>
                    </div>
                </div>
            </div>

            <dl class="mt-20 grid grid-cols-3 border-t border-night">
                <div v-for="f in figures" :key="f.label" class="flex flex-col-reverse border-r border-line-light pt-6 pr-4 last:border-r-0 [&:not(:first-child)]:pl-4 md:[&:not(:first-child)]:pl-8">
                    <dt class="mt-2 text-sm font-medium text-ink-2 md:text-base">{{ f.label }}</dt>
                    <dd class="font-display text-[clamp(2.75rem,7vw,6rem)] leading-none font-black text-green tabular-nums"><RollingNumber :value="f.value" /></dd>
                </div>
            </dl>
        </div>
    </section>
</template>

<style scoped>
/* Same marker and stamp as the landing's WhatIsSection */
.marker {
    color: inherit;
    background: linear-gradient(var(--gold), var(--gold)) no-repeat 0 85% / 100% 100%;
    padding-inline: 0.06em;
    box-decoration-break: clone;
    -webkit-box-decoration-break: clone;
}
.stamp {
    border: 2px solid var(--red);
    color: var(--red);
    padding: 12px 16px;
    font-size: 1.25rem; /* large text: red on snow passes 3:1 */
    font-weight: 700;
    transform: rotate(-8deg);
    outline: 1px dashed var(--red);
    outline-offset: 4px;
}
</style>
