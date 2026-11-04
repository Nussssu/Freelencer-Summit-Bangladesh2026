<script setup lang="ts">
import { computed, ref } from 'vue';
import AboutClosing from '@/components/about/AboutClosing.vue';
import ExhibitionHero from '@/components/exhibition/ExhibitionHero.vue';
import FloorPlanDialog from '@/components/exhibition/FloorPlanDialog.vue';
import FloorPlanMap from '@/components/exhibition/FloorPlanMap.vue';
import type { Booth } from '@/components/exhibition/booths';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, Option, SectionHeading } from '@/types/inner';

// Brief §11, laid out after the ai-expo.net "Sponsor or Exhibit" page, headings and actions only (no body copy): short
// photo hero, audience figures, why exhibit, who should exhibit (link to the full exhibitor list), floor plan teaser
// that opens the zoomable plan, and the About-style closing band. One ground under the body, as on the About page.
const props = defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'audience' | 'why' | 'who' | 'floor' | 'cta', SectionHeading>;
        why: { title: string; icon: GlyphName; tone: string; photo: string }[];
        who: { title: string; icon: GlyphName }[];
        booths: Booth[];
        stats: { value: string; label: string }[];
        legend: Option[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const photos = ['/media/photos/exhibit-booth.jpg', '/media/photos/highlight-exhibition.jpg', '/media/photos/highlight-marketplace.jpg', '/media/photos/sponsor-hall.jpg'];
const apply = computed(() => props.page.hero.actions?.find((a) => a.tone === 'gold') ?? { label: 'Exhibit at Summit', href: '/exhibition/apply' });
const planOpen = ref(false);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <ExhibitionHero :page="page" action />

    <div ref="root" class="ground on-light">
        <!-- Audience figures -->
        <section class="in-section" aria-labelledby="ex-audience">
            <div class="container-site text-center">
                <SplitHeading id="ex-audience" :text="content.headings.audience.title" :emphasis="content.headings.audience.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 mx-auto max-w-4xl" />
                <dl class="mt-12 grid gap-8 sm:grid-cols-3">
                    <div v-for="s in content.stats" :key="s.label" class="flex flex-col-reverse gap-2" data-reveal="fade">
                        <dt class="text-sm font-bold tracking-wide text-ink-2 uppercase">{{ s.label }}</dt>
                        <dd class="font-display text-[clamp(3rem,6vw,4.75rem)] leading-none font-black text-green">{{ s.value }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Why exhibit: photo cards -->
        <section class="in-section" aria-labelledby="ex-why">
            <div class="container-site">
                <SplitHeading id="ex-why" :text="content.headings.why.title" :emphasis="content.headings.why.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 text-center" />
                <ul class="mt-12 grid gap-6 md:grid-cols-3">
                    <li v-for="w in content.why" :key="w.title" class="group text-center" data-reveal="fade">
                        <span class="block aspect-[3/2] overflow-hidden rounded-xl">
                            <img :src="w.photo" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        </span>
                        <p class="mx-auto mt-5 max-w-[22rem] text-lg leading-snug font-extrabold text-night">{{ w.title }}</p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Who should exhibit: photo cards -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.who"><InBtn href="/exhibition/exhibitors">See all exhibitors</InBtn></SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(w, i) in content.who" :key="w.title" class="in-card is-solid overflow-hidden" data-reveal="fade">
                        <span class="in-media aspect-[4/3]"><img :src="photos[i % 4]" alt="" loading="lazy" /></span>
                        <p class="in-h3 p-5 text-lg!">{{ w.title }}</p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Floor plan teaser: the preview opens the zoomable plan -->
        <section class="in-section" aria-labelledby="ex-floor">
            <div class="container-site grid gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-5">
                    <SplitHeading id="ex-floor" :text="content.headings.floor.title" :emphasis="content.headings.floor.emphasis ?? undefined" emphasis-class="text-green" class="in-h2" />
                    <div class="mt-8 flex flex-wrap gap-3" data-reveal="fade">
                        <InBtn :href="apply.href" tone="gold">Book a booth</InBtn>
                    </div>
                </div>
                <button type="button" class="ex-preview group relative block overflow-hidden rounded-2xl bg-white p-3 text-left ring-1 ring-night/10 lg:col-span-7" aria-label="Open the floor plan" data-reveal="fade" @click="planOpen = true">
                    <FloorPlanMap :booths="content.booths" />
                    <span class="absolute inset-0 grid place-items-center bg-night/0 transition-colors duration-300 group-hover:bg-night/35" aria-hidden="true">
                        <span class="flex translate-y-2 items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-night opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6" /><path d="m20 20-4.5-4.5M11 8.5v5M8.5 11h5" /></svg>
                            Click to zoom
                        </span>
                    </span>
                </button>
            </div>
        </section>

        <AboutClosing :heading="content.headings.cta" :page="page">
            <CtaLink :href="apply.href" class="!px-7 !py-4">{{ apply.label }}</CtaLink>
        </AboutClosing>
    </div>

    <FloorPlanDialog v-model:open="planOpen" :booths="content.booths" :legend="content.legend" />
</template>

<style scoped>
/* The About page's ground: ice blue with soft teal and summit-gold glows alternating down the page */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
.ex-preview {
    box-shadow: 0 30px 60px -34px rgb(7 20 17 / 0.45);
}
</style>
