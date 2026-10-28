<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Dialog from '@/components/ui/Dialog.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Media } from '@/types/content';

// Section 15. Dense duotone mosaic (CSS columns masonry), staggered clip reveal, lightbox, video row.
const props = defineProps<{ media: Media; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const current = ref<number | null>(null);
const open = computed({ get: () => current.value !== null, set: (v) => !v && (current.value = null) });
// Two tall tiles in every run of four, staggered, so CSS columns end level.
const tall = (id: number) => (id + Math.floor(id / 4)) % 2 === 0;
const label = (tile: Media['tiles'][number]) => tile.caption ?? t('sections.photoSoon', { programme: tile.programme });
useReveal(root);
const step = (d: number) => current.value !== null && (current.value = (current.value + d + props.media.tiles.length) % props.media.tiles.length);
</script>

<template>
    <section ref="root" data-section="15" aria-labelledby="md-title" class="on-light section relative z-[1] overflow-hidden">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="t-label text-green">{{ t('sections.media') }}</p>
                    <SplitHeading id="md-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3" />
                </div>
                <Link href="/media" class="link-u t-label inline-flex min-h-11 items-center gap-2">{{ t('sections.allMedia') }} <Icon name="arrow" :size="16" /></Link>
            </div>

            <ul class="mt-12 columns-2 gap-3 md:columns-4">
                <li v-for="tile in media.tiles" :key="tile.id" class="mb-3 break-inside-avoid">
                    <button
                        type="button"
                        class="duotone group relative block w-full overflow-hidden text-left"
                        :class="tall(tile.id) ? 'aspect-[3/4]' : 'aspect-square'"
                        :style="tile.src ? undefined : { filter: `hue-rotate(${(tile.id % 4) * 12}deg)` }"
                        data-reveal="clip"
                        :aria-label="t('sections.openImage', { programme: tile.programme, n: tile.id + 1 })"
                        @click="current = tile.id"
                    >
                        <img v-if="tile.src" :src="tile.src" alt="" loading="lazy" class="h-full w-full object-cover" />
                        <span class="t-label absolute bottom-2 left-2 bg-night/80 px-2 py-1 text-paper">{{ tile.programme }}</span>
                        <span v-if="tile.kind === 'video'" class="absolute top-2 right-2 grid size-9 place-items-center bg-gold text-night" aria-hidden="true"><Icon name="play" :size="16" /></span>
                    </button>
                </li>
            </ul>

            <h3 class="t-label mt-16 text-green">{{ t('sections.videos') }}</h3>
            <ul class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                <li v-for="v in media.videos" :key="v.title" class="flex items-center gap-4 border border-line-light bg-paper-2 p-4">
                    <span class="grid size-14 shrink-0 place-items-center border border-green text-green" aria-hidden="true"><Icon name="play" :size="20" /></span>
                    <div>
                        <p class="font-display text-xl font-black uppercase">{{ v.title }}</p>
                        <p class="t-label text-ink-2">{{ v.youtubeId ? '' : t('sections.videoSoon') }}</p>
                    </div>
                </li>
            </ul>
        </div>

        <Dialog v-model:open="open" :label="current !== null ? label(media.tiles[current]) : ''" variant="modal">
            <div v-if="current !== null" @keydown.left="step(-1)" @keydown.right="step(1)">
                <div class="duotone aspect-video w-full" :style="media.tiles[current].src ? undefined : { filter: `hue-rotate(${(current % 4) * 12}deg)` }" role="img" :aria-label="label(media.tiles[current])">
                    <img v-if="media.tiles[current].src" :src="media.tiles[current].src!" alt="" class="h-full w-full object-cover" />
                </div>
                <p class="mt-4">{{ label(media.tiles[current]) }}</p>
                <div class="mt-4 flex gap-2">
                    <button type="button" class="grid size-11 place-items-center border border-line-dark hover:text-gold" :aria-label="t('common.previous')" @click="step(-1)"><Icon name="arrow-left" /></button>
                    <button type="button" class="grid size-11 place-items-center border border-line-dark hover:text-gold" :aria-label="t('common.next')" @click="step(1)"><Icon name="arrow" /></button>
                </div>
            </div>
        </Dialog>
    </section>
</template>
