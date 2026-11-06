<script setup lang="ts">
import { computed, ref } from 'vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import AboutHero from '@/components/about/AboutHero.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { DownloadItem, InnerPage, Option, SectionHeading } from '@/types/inner';

type Tile = { id: number; programme: string; programmeLabel: string; kind: string; src: string; caption: string | null };
// Brief §16: the About-style photo hero, then the photo gallery (a programme filter box on the left as on the Agenda,
// the photos beside it), the video gallery and the press and media kit downloads.
const props = defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'photos' | 'videos' | 'press', SectionHeading>;
        tiles: Tile[];
        programmes: Option[];
        videos: { title: string; youtubeId: string | null }[];
        note: string;
        downloads: DownloadItem[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const programme = ref('');
const filtersOpen = ref(false);
const shown = computed(() => props.content.tiles.filter((x) => !programme.value || x.programme === programme.value));
const count = (p: string) => props.content.tiles.filter((x) => x.programme === p).length;
const open = ref(false);
const current = ref<Tile | null>(null);
const view = (x: Tile) => ((current.value = x), (open.value = true));
// Step through the photos the filter shows, wrapping at either end; the arrow keys do the same while the popup is open.
const step = (d: number) => {
    const i = shown.value.findIndex((x) => x.id === current.value?.id);
    current.value = shown.value[(i + d + shown.value.length) % shown.value.length];
};
const onKey = (e: KeyboardEvent) => {
    if (e.key === 'ArrowLeft') step(-1);
    if (e.key === 'ArrowRight') step(1);
};
const posters = ['/media/photos/gallery-07.jpg', '/media/photos/gallery-10.jpg', '/media/photos/highlight-career.jpg'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root" class="ground on-light">
        <!-- Photo gallery: programme filter box on the left (as on the Agenda), the photos beside it. Below xl the box
             folds behind a Filter button. -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.photos" />
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:gap-8">
                    <button type="button" class="in-btn is-ghost min-h-11! gap-2! self-start px-4! py-2! xl:hidden" :aria-expanded="filtersOpen" aria-controls="md-filters" @click="filtersOpen = !filtersOpen">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                        Filter
                    </button>

                    <aside id="md-filters" class="w-full shrink-0 xl:sticky xl:top-[calc(153px+1.5rem)] xl:block xl:w-[20rem]" :class="!filtersOpen && 'hidden'" aria-label="Filter photos">
                        <div class="in-card is-solid gap-5 p-6">
                            <h3 class="text-lg font-semibold text-green">Programme</h3>
                            <div class="flex flex-col gap-2.5" role="group" aria-label="Programme">
                                <button type="button" class="md-pill" :aria-pressed="!programme" @click="programme = ''">All photos<span class="md-count">{{ content.tiles.length }}</span></button>
                                <button v-for="o in content.programmes" :key="o.value" type="button" class="md-pill" :aria-pressed="programme === o.value" @click="programme = programme === o.value ? '' : o.value">
                                    {{ o.label }}<span class="md-count">{{ count(o.value) }}</span>
                                </button>
                            </div>
                            <button type="button" class="md-clear" :disabled="!programme" @click="programme = ''">Clear filter</button>
                            <p class="in-meta border-t border-night/8 pt-4 text-xs!">{{ content.note }}</p>
                        </div>
                    </aside>

                    <div class="w-full min-w-0">
                        <p class="in-meta mb-4" aria-live="polite">{{ shown.length }} {{ shown.length === 1 ? 'photo' : 'photos' }}</p>
                        <ul v-if="shown.length" class="grid grid-cols-2 gap-3 md:grid-cols-3">
                            <li v-for="x in shown" :key="x.id">
                                <button type="button" class="in-media is-round group block aspect-square w-full" :aria-label="`View photo: ${x.programmeLabel}`" @click="view(x)">
                                    <img :src="x.src" alt="" loading="lazy" class="transition-transform duration-700 group-hover:scale-105" />
                                    <span class="absolute bottom-3 left-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-night">{{ x.programmeLabel }}</span>
                                </button>
                            </li>
                        </ul>
                        <EmptyState v-else level="h3" icon="image" title="No photos here yet" body="Photos are added once programmes have taken place.">
                            <button type="button" class="in-btn is-ghost" @click="programme = ''">Clear filter</button>
                        </EmptyState>
                    </div>
                </div>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.videos" />
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="(v, i) in content.videos" :key="v.title" class="in-card is-solid overflow-hidden" data-reveal="fade">
                        <span class="in-media aspect-video">
                            <iframe v-if="v.youtubeId" :src="`https://www.youtube-nocookie.com/embed/${v.youtubeId}`" :title="v.title" class="absolute inset-0 h-full w-full" allowfullscreen loading="lazy" />
                            <template v-else>
                                <img :src="posters[i % 3]" alt="" loading="lazy" />
                                <span class="absolute inset-0 grid place-items-center bg-night/30"><span class="rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-night">Video coming soon</span></span>
                            </template>
                        </span>
                        <h3 class="in-h3 p-5 text-lg!">{{ v.title }}</h3>
                    </li>
                </ul>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.press" />
                <ul class="grid gap-4 md:grid-cols-2">
                    <li v-for="(d, i) in content.downloads" :key="d.kind" data-reveal="fade"><DownloadCard :item="d" :tone="['tone-teal', 'tone-sun'][i % 2]" /></li>
                </ul>
            </div>
        </section>
    </div>

    <Dialog v-model:open="open" variant="modal" :label="current?.programmeLabel ?? 'Photo'" tone="light">
        <figure v-if="current" @keydown="onKey">
            <div class="relative">
                <img :src="current.src" alt="" class="max-h-[65svh] w-full rounded-2xl object-contain" />
                <template v-if="shown.length > 1">
                    <button type="button" class="md-nav left-3" aria-label="Previous photo" @click="step(-1)"><Icon name="arrow-left" :size="18" /></button>
                    <button type="button" class="md-nav right-3" aria-label="Next photo" @click="step(1)"><Icon name="arrow" :size="18" /></button>
                </template>
            </div>
            <figcaption class="mt-4 flex items-baseline justify-between gap-4">
                <span class="font-semibold">{{ current.caption ?? current.programmeLabel }}</span>
                <span v-if="shown.length > 1" class="in-meta shrink-0">{{ shown.findIndex((x) => x.id === current?.id) + 1 }} / {{ shown.length }}</span>
            </figcaption>
        </figure>
    </Dialog>
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
/* Programme filter: the Agenda's stage pills and clear button, with a photo count on each pill */
.md-pill {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border: 1px solid rgb(7 20 17 / 0.15);
    border-radius: 999px;
    padding: 0.5rem 0.5rem 0.5rem 1.25rem;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink-2);
    transition:
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}
.md-pill:hover,
.md-pill[aria-pressed='true'] {
    border-color: transparent;
    background: linear-gradient(90deg, #06454c, var(--green));
    color: var(--white);
}
.md-count {
    min-width: 1.75rem;
    border-radius: 999px;
    background: rgb(8 127 140 / 0.1);
    padding: 0.125rem 0.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: var(--green);
}
.md-pill:hover .md-count,
.md-pill[aria-pressed='true'] .md-count {
    background: rgb(255 255 255 / 0.2);
    color: var(--white);
}
.md-clear {
    width: 100%;
    border: 1px solid var(--green);
    border-radius: 0.75rem;
    padding: 0.5rem;
    font-weight: 600;
    color: var(--green);
    transition:
        background 0.2s ease,
        color 0.2s ease,
        opacity 0.2s ease;
}
.md-clear:hover:not(:disabled) {
    background: linear-gradient(90deg, var(--green), #06454c);
    color: var(--white);
}
.md-clear:disabled {
    cursor: default;
    opacity: 0.45;
}
/* Popup arrows over the photo */
.md-nav {
    position: absolute;
    top: 50%;
    display: grid;
    width: 2.75rem;
    height: 2.75rem;
    place-items: center;
    border-radius: 999px;
    background: rgb(255 255 255 / 0.9);
    color: var(--night);
    transform: translateY(-50%);
    box-shadow: 0 8px 20px -10px rgb(7 20 17 / 0.5);
    transition:
        background 0.2s ease,
        color 0.2s ease;
}
.md-nav:hover {
    background: var(--green);
    color: var(--white);
}
</style>
