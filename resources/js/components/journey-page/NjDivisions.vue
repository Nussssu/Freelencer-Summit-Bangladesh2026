<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { JourneyContent, SectionHeading } from '@/types/inner';

// Eight divisional summits in route order. Hover/focus: the card lifts, a teal → bronze rule draws across the top,
// a deep-green wash rises over the photo. Held vs upcoming is the only status shown.
defineProps<{ heading: SectionHeading; summits: JourneyContent['divisionSummits']; href: string }>();
const root = ref<HTMLElement>();
useReveal(root);
const fmt = (n: number) => n.toLocaleString('en-US');
</script>

<template>
    <section ref="root" class="section" aria-labelledby="nj-divisions">
        <div class="container-site">
            <SectionHead id="nj-divisions" :heading="heading" />
            <ul class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="d in summits" :key="d.name" data-reveal="fade">
                    <article
                        class="group relative flex h-full flex-col border border-line-light bg-paper transition-[transform,box-shadow,border-color] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-green/30 hover:shadow-[0_28px_50px_-26px_rgb(8_58_64/0.45)] focus-within:-translate-y-2 focus-within:shadow-[0_28px_50px_-26px_rgb(8_58_64/0.45)]"
                    >
                        <span class="absolute inset-x-0 top-0 z-10 h-[3px] origin-left scale-x-0 bg-gradient-to-r from-green to-gold transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-focus-within:scale-x-100 group-hover:scale-x-100" aria-hidden="true" />
                        <div class="relative overflow-hidden">
                            <Photo :src="d.image" :alt="`${d.name} divisional summit`" class="aspect-[16/10] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.06]" />
                            <div class="absolute inset-0 bg-gradient-to-t from-delta/80 via-delta/25 to-transparent opacity-70 transition-opacity duration-500 group-hover:opacity-90" aria-hidden="true" />
                            <p class="t-label absolute top-3 left-3 px-2 py-1" :class="d.held ? 'bg-snow/90 text-delta' : 'bg-gold text-night'">{{ d.held ? 'Held' : 'Upcoming' }}</p>
                            <p class="absolute right-3 bottom-3 left-3 flex items-center gap-2 text-sm font-semibold text-snow">
                                <Icon name="calendar" :size="14" /> <time>{{ d.date }}</time>
                            </p>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="font-display text-[2rem] leading-none font-black uppercase">
                                <Link :href="href" class="outline-none transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green focus-visible:text-green">{{ d.name }}</Link>
                            </h3>
                            <p class="mt-2 text-sm text-ink-2">{{ d.venue }}</p>
                            <dl class="mt-auto grid grid-cols-3 gap-2 border-t border-line-light pt-4 text-sm">
                                <div><dt class="text-ink-2">Districts</dt><dd class="mt-0.5 font-bold tabular-nums">{{ d.districts }}</dd></div>
                                <div><dt class="text-ink-2">Campuses</dt><dd class="mt-0.5 font-bold tabular-nums">{{ d.campuses }}</dd></div>
                                <div><dt class="text-ink-2">{{ d.held ? 'Attended' : 'Expected' }}</dt><dd class="mt-0.5 font-bold tabular-nums">{{ fmt(d.participants) }}</dd></div>
                            </dl>
                        </div>
                        <span class="pointer-events-none absolute inset-0 opacity-0 ring-2 ring-green ring-offset-2 ring-offset-snow transition-opacity group-has-[:focus-visible]:opacity-100" aria-hidden="true" />
                    </article>
                </li>
            </ul>
        </div>
    </section>
</template>
