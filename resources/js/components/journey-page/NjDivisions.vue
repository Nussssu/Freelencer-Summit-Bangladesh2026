<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { JourneyContent, SectionHeading } from '@/types/inner';

// Eight divisional summits in route order, as rounded cards (client mock-up): photo with a Held/Upcoming pill, then date,
// name, venue and teal counts. Hover/focus: the card lifts and a teal → gold rule draws along the bottom.
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
                        class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-white/80 bg-paper shadow-[0_18px_40px_-28px_rgb(8_58_64/0.35)] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:shadow-[0_28px_50px_-26px_rgb(8_58_64/0.45)] focus-within:-translate-y-2 focus-within:shadow-[0_28px_50px_-26px_rgb(8_58_64/0.45)]"
                    >
                        <div class="relative overflow-hidden">
                            <Photo :src="d.image" :alt="`${d.name} divisional summit`" class="aspect-[16/9] transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.06]" />
                            <p class="absolute top-4 left-4 rounded-full px-3.5 py-1 text-sm font-bold tracking-[0.04em] uppercase shadow-sm" :class="d.held ? 'bg-[#dff3f3] text-green' : 'bg-gold text-night'">{{ d.held ? 'Held' : 'Upcoming' }}</p>
                        </div>
                        <div class="flex flex-1 flex-col px-5 pt-4 pb-5">
                            <p class="flex items-center gap-2 text-sm font-semibold text-night"><Icon name="calendar" :size="16" class="text-green" /> <time>{{ d.date }}</time></p>
                            <h3 class="mt-2 text-[1.75rem] leading-none font-extrabold tracking-[-0.01em] uppercase">
                                <Link :href="href" class="outline-none transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green focus-visible:text-green">{{ d.name }}</Link>
                            </h3>
                            <p class="mt-1.5 text-ink-2">{{ d.venue }}</p>
                            <dl class="mt-auto grid grid-cols-3 gap-2 border-t border-line-light pt-4">
                                <div><dt class="text-sm text-ink-2">Districts</dt><dd class="mt-0.5 text-2xl font-extrabold text-green tabular-nums">{{ d.districts }}</dd></div>
                                <div><dt class="text-sm text-ink-2">Campuses</dt><dd class="mt-0.5 text-2xl font-extrabold text-green tabular-nums">{{ d.campuses }}</dd></div>
                                <div><dt class="text-sm text-ink-2">{{ d.held ? 'Attended' : 'Expected' }}</dt><dd class="mt-0.5 text-2xl font-extrabold text-green tabular-nums">{{ fmt(d.participants) }}</dd></div>
                            </dl>
                        </div>
                        <span class="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-green to-gold transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-focus-within:scale-x-100 group-hover:scale-x-100" aria-hidden="true" />
                        <span class="pointer-events-none absolute inset-0 rounded-2xl opacity-0 ring-2 ring-green ring-inset transition-opacity group-has-[:focus-visible]:opacity-100" aria-hidden="true" />
                    </article>
                </li>
            </ul>
        </div>
    </section>
</template>
