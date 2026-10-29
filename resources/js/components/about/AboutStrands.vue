<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// The four strands as one route: campus → district → Grand Dhaka Summit → digital campaign, joined by the kantha
// stitch (horizontal on desktop, a vertical dashed spine on mobile). Order and photos are presentation only.
const props = defineProps<{ heading: SectionHeading; phases: AboutContent['phases'] }>();
const root = ref<HTMLElement>();
useReveal(root);

// Route order and supporting photos per docs/image-map.md (/about: highlight-networking, gallery-01..03).
const ORDER = ['/campus-programs', '/district-roadshows', '/grand-summit', '/news'];
const PHOTOS: Record<string, string> = {
    '/campus-programs': '/media/photos/gallery-01.jpg',
    '/district-roadshows': '/media/photos/gallery-02.jpg',
    '/grand-summit': '/media/photos/highlight-networking.jpg',
    '/news': '/media/photos/gallery-03.jpg',
};
const strands = computed(() => [...props.phases].sort((a, b) => ORDER.indexOf(a.href) - ORDER.indexOf(b.href)));
</script>

<template>
    <section ref="root" class="section bg-mist" aria-labelledby="ab-phases">
        <div class="container-site">
            <AboutHead id="ab-phases" :heading="heading" class="max-w-3xl" />

            <div class="relative mt-16">
                <div class="stitch absolute top-[7px] right-0 left-0 hidden xl:block" data-reveal="stitch" aria-hidden="true" />
                <ol class="relative grid gap-10 border-l-2 border-dashed border-gold pl-6 md:grid-cols-2 md:gap-6 md:border-l-0 md:pl-0 xl:grid-cols-4">
                    <li v-for="p in strands" :key="p.href" class="relative flex flex-col" data-reveal="fade">
                        <span class="absolute top-0 -left-[33px] size-4 border-2 border-mist md:static md:mb-6" :class="p.href === '/grand-summit' ? 'bg-red' : 'bg-green'" aria-hidden="true" />
                        <article class="group relative flex flex-1 flex-col bg-snow shadow-[0_24px_48px_-28px_rgb(8_58_64/0.35)] transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] focus-within:-translate-y-1.5 hover:-translate-y-1.5">
                            <div class="overflow-hidden">
                                <Photo :src="PHOTOS[p.href]" class="aspect-[4/3] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.04]" />
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <p class="t-label text-ink-2">{{ p.coverage }}</p>
                                <h3 class="mt-3 font-display text-[2rem] leading-[0.95] font-black uppercase">
                                    <Link :href="p.href" class="outline-none after:absolute after:inset-0 group-hover:text-green focus-visible:text-green">{{ p.title }}</Link>
                                </h3>
                                <ul class="mt-5 flex-1 space-y-2 text-ink-2">
                                    <li v-for="a in p.activities" :key="a" class="flex gap-2.5"><span class="mt-[0.6rem] h-px w-3 shrink-0 bg-green" aria-hidden="true" />{{ a }}</li>
                                </ul>
                                <Icon name="arrow" class="mt-6 text-green transition-transform duration-500 group-hover:translate-x-1.5" />
                            </div>
                            <span class="pointer-events-none absolute inset-0 ring-2 ring-green ring-offset-2 ring-offset-mist opacity-0 transition-opacity group-has-[:focus-visible]:opacity-100" aria-hidden="true" />
                        </article>
                    </li>
                </ol>
            </div>
        </div>
    </section>
</template>
