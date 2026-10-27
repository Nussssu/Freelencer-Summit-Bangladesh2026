<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Stories } from '@/types/content';

// Section 12. Bento grid (video / quote / stat tiles) in honest "Story coming soon" states, plus a quote
// carousel with programme tags (Take Off T8). YouTube loads only after a click.
defineProps<{ stories: Stories; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const playing = ref<string | null>(null);
const quote = ref(0);
useReveal(root);
const span = ['md:col-span-2 md:row-span-2', '', '', 'md:col-span-2', '', ''];
</script>

<template>
    <section ref="root" data-section="12" aria-labelledby="st-title" class="on-light section relative z-[1] bg-mist">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="t-label text-green">{{ t('sections.stories') }}</p>
                    <SplitHeading id="st-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3" />
                </div>
                <p v-if="stories.sample" class="t-label bg-gold px-2 py-1 text-night">{{ t('common.sample') }}</p>
            </div>

            <ul class="mt-12 grid auto-rows-[minmax(11rem,auto)] grid-cols-1 gap-3 md:grid-cols-4">
                <li v-for="(tile, i) in stories.tiles" :key="i" class="relative flex flex-col justify-between overflow-hidden border border-line-light p-5" :class="[span[i], tile.kind === 'video' ? 'duotone text-paper' : 'bg-paper']" data-reveal="clip">
                    <p class="t-label" :class="tile.kind === 'video' ? 'text-mint' : 'text-green'">{{ tile.role }}</p>
                    <template v-if="tile.kind === 'video'">
                        <iframe v-if="playing === tile.youtubeId && tile.youtubeId" class="absolute inset-0 h-full w-full" :src="`https://www.youtube-nocookie.com/embed/${tile.youtubeId}?autoplay=1`" :title="tile.role" allow="autoplay; encrypted-media" allowfullscreen />
                        <button type="button" class="flex items-center gap-4 self-start" :disabled="!tile.youtubeId" :aria-label="t('sections.playVideo', { title: tile.role })" @click="playing = tile.youtubeId">
                            <span class="grid size-16 place-items-center bg-gold text-night"><Icon name="play" :size="24" /></span>
                            <span class="font-display text-3xl font-black uppercase">{{ tile.youtubeId ? tile.role : t('common.storySoon') }}</span>
                        </button>
                    </template>
                    <blockquote v-else-if="tile.kind === 'quote'" class="font-display text-2xl leading-tight font-black uppercase" :class="!tile.quote && 'text-ink-2/60'">“{{ tile.quote ?? t('common.storySoon') }}”</blockquote>
                    <div v-else>
                        <p class="t-stat text-gold-ink">{{ tile.value ?? '0+' }}</p>
                        <p class="mt-2 text-sm text-ink-2">{{ tile.label }} · {{ tile.value ? '' : t('sections.valueSoon') }}</p>
                    </div>
                </li>
            </ul>

            <div class="mt-16 grid grid-cols-1 gap-6 border-t border-line-light pt-8 md:grid-cols-12" aria-roledescription="carousel" :aria-label="t('sections.quotes')">
                <h3 class="t-label text-green md:col-span-3">{{ t('sections.quotes') }}</h3>
                <div class="min-w-0 overflow-hidden md:col-span-7">
                    <div class="flex transition-transform duration-[800ms] ease-out-expo" :style="{ transform: `translateX(${-quote * 100}%)` }">
                        <figure v-for="(q, i) in stories.carousel" :key="i" class="w-full shrink-0 pr-6" :aria-hidden="i !== quote" aria-roledescription="slide">
                            <span class="t-label border border-green px-2 py-1 text-green">{{ q.programme }}</span>
                            <blockquote class="mt-4 font-display text-4xl leading-tight font-black uppercase" :class="!q.quote && 'text-ink-2/60'">“{{ q.quote ?? t('common.storySoon') }}”</blockquote>
                            <figcaption class="mt-3 text-sm text-ink-2">{{ q.role }}</figcaption>
                        </figure>
                    </div>
                </div>
                <div class="flex items-start gap-2 md:col-span-2 md:justify-end">
                    <button type="button" class="grid size-11 place-items-center border border-line-light transition-colors duration-300 hover:border-green hover:text-green" :aria-label="t('common.previous')" @click="quote = (quote + stories.carousel.length - 1) % stories.carousel.length"><Icon name="arrow-left" /></button>
                    <button type="button" class="grid size-11 place-items-center border border-line-light transition-colors duration-300 hover:border-green hover:text-green" :aria-label="t('common.next')" @click="quote = (quote + 1) % stories.carousel.length"><Icon name="arrow" /></button>
                </div>
            </div>
        </div>
    </section>
</template>
