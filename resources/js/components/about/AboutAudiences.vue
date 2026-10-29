<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { Audience } from '@/types/content';
import type { SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// Segmented selector + detail panel instead of nine identical cards (WAI-ARIA tabs, arrow keys / Home / End).
// Every panel is rendered (v-show), so all bullets and links stay in the server HTML.
const props = defineProps<{ heading: SectionHeading; audiences: Audience[] }>();
const root = ref<HTMLElement>();
useReveal(root);

const active = ref(0);
const tabs: HTMLButtonElement[] = [];
function onKey(e: KeyboardEvent, i: number) {
    const n = props.audiences.length;
    const to = { ArrowRight: i + 1, ArrowDown: i + 1, ArrowLeft: i - 1, ArrowUp: i - 1, Home: 0, End: n - 1 }[e.key];
    if (to === undefined) return;
    e.preventDefault();
    active.value = (to + n) % n;
    tabs[active.value]?.focus();
}
</script>

<template>
    <section ref="root" class="section" aria-labelledby="ab-audiences">
        <div class="container-site">
            <AboutHead id="ab-audiences" :heading="heading" class="max-w-3xl" />

            <div class="mt-14 grid gap-8 lg:grid-cols-12 lg:gap-10">
                <div role="tablist" aria-labelledby="ab-audiences" class="flex flex-wrap gap-2 lg:col-span-4 lg:flex-col lg:gap-0 lg:border-t lg:border-night" data-reveal="fade">
                    <button
                        v-for="(a, i) in audiences"
                        :id="`ab-aud-tab-${i}`"
                        :key="a.title"
                        :ref="(el) => (tabs[i] = el as HTMLButtonElement)"
                        type="button"
                        role="tab"
                        :aria-selected="active === i"
                        :aria-controls="`ab-aud-panel-${i}`"
                        :tabindex="active === i ? 0 : -1"
                        class="tab group flex min-h-11 items-center justify-between gap-4 border px-4 text-left font-semibold transition-colors duration-300 lg:min-h-14 lg:border-0 lg:border-b lg:border-line-light lg:px-0 lg:text-xl"
                        @click="active = i"
                        @keydown="onKey($event, i)"
                    >
                        <span class="lg:transition-transform lg:duration-500 lg:ease-[cubic-bezier(0.16,1,0.3,1)] lg:group-aria-selected:translate-x-3">{{ a.title }}</span>
                        <Icon name="arrow" :size="18" class="hidden text-green opacity-0 transition-opacity duration-300 lg:block lg:group-aria-selected:opacity-100" />
                    </button>
                </div>

                <div class="lg:col-span-8">
                    <div
                        v-for="(a, i) in audiences"
                        v-show="active === i"
                        :id="`ab-aud-panel-${i}`"
                        :key="a.title"
                        role="tabpanel"
                        :aria-labelledby="`ab-aud-tab-${i}`"
                        tabindex="0"
                        class="panel relative overflow-hidden bg-snow p-8 shadow-[0_24px_48px_-32px_rgb(8_58_64/0.4)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-green md:p-12"
                    >
                        <span class="pointer-events-none absolute -right-4 -bottom-10 font-display text-[11rem] leading-none font-black text-mist select-none md:text-[15rem]" aria-hidden="true">{{ String(i + 1).padStart(2, '0') }}</span>
                        <div class="relative">
                            <h3 class="font-display text-[clamp(2.25rem,4vw,3.5rem)] leading-[0.95] font-black uppercase">{{ a.title }}</h3>
                            <div class="stitch mt-6 w-24" aria-hidden="true" />
                            <ul class="mt-8 max-w-[52ch] space-y-4 text-lg">
                                <li v-for="b in a.benefits" :key="b" class="flex gap-3"><span class="mt-[0.7rem] h-px w-4 shrink-0 bg-green" aria-hidden="true" />{{ b }}</li>
                            </ul>
                            <Link :href="a.href" class="btn btn-green mt-10">{{ a.cta }} <Icon name="arrow" :size="16" /></Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.tab {
    border-color: var(--line-light);
    background: var(--snow);
}
.tab:hover {
    color: var(--green);
}
.tab[aria-selected='true'] {
    background: var(--night);
    border-color: var(--night);
    color: var(--paper);
}
@media (min-width: 1024px) {
    .tab,
    .tab[aria-selected='true'] {
        background: transparent;
    }
    .tab[aria-selected='true'] {
        color: var(--green);
        border-color: var(--green);
    }
}
.tab:focus-visible {
    outline: 2px solid var(--green);
    outline-offset: 2px;
}
.panel {
    animation: panel-in 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes panel-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
}
@media (prefers-reduced-motion: reduce) {
    .panel {
        animation: none;
    }
}
</style>
