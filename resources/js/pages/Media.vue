<script setup lang="ts">
import { computed, ref } from 'vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Dialog from '@/components/ui/Dialog.vue';
import { useReveal } from '@/composables/useReveal';
import type { DownloadItem, InnerPage, Option, SectionHeading } from '@/types/inner';

type Tile = { id: number; programme: string; programmeLabel: string; kind: string; src: string; caption: string | null };
// Brief §16: photo and video galleries by programme (divisional, district, campus), press and media kit downloads.
const props = defineProps<{
    page: InnerPage;
    content: { headings: Record<'photos' | 'videos' | 'press', SectionHeading>; tiles: Tile[]; programmes: Option[]; videos: { title: string; youtubeId: string | null }[]; sample: boolean; note: string; downloads: DownloadItem[] };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const programme = ref('');
const shown = computed(() => props.content.tiles.filter((x) => !programme.value || x.programme === programme.value));
const open = ref(false);
const current = ref<Tile | null>(null);
const view = (x: Tile) => ((current.value = x), (open.value = true));
const posters = ['/media/photos/gallery-07.jpg', '/media/photos/gallery-10.jpg', '/media/photos/highlight-career.jpg'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.photos"><FilterChips v-model="programme" :options="content.programmes" label="Programme" /></SectionHead>
                <ul class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                    <li v-for="(x, i) in shown" :key="x.id" :class="i % 7 === 0 && 'md:col-span-2 md:row-span-2'">
                        <button type="button" class="in-media is-round group block aspect-square h-full w-full" :aria-label="`View photo: ${x.programmeLabel}`" @click="view(x)">
                            <img :src="x.src" alt="" loading="lazy" class="transition-transform duration-700 group-hover:scale-105" />
                            <span class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-night">{{ x.programmeLabel }}</span>
                        </button>
                    </li>
                </ul>
                <div class="mt-4 flex flex-wrap items-center gap-3"><SampleRibbon v-if="content.sample" /><p class="in-meta">{{ content.note }}</p></div>
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

    <Dialog v-model:open="open" :label="current?.programmeLabel ?? 'Photo'" tone="light">
        <img v-if="current" :src="current.src" alt="" class="w-full rounded-2xl" />
        <p v-if="current" class="mt-4 font-semibold">{{ current.caption ?? current.programmeLabel }}</p>
    </Dialog>

    <GetInvolved />
</template>
