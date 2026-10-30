<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import PersonCard from '@/components/inner/PersonCard.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TbcNotice from '@/components/inner/TbcNotice.vue';
import TintCard from '@/components/inner/TintCard.vue';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Countdown from '@/components/ui/Countdown.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { DownloadItem, InnerPage, SectionHeading, SpeakerCard } from '@/types/inner';
import type { Highlight } from '@/types/content';

// Brief §7 event module: date, venue, map and countdown, highlights, agenda preview, speakers, awards/exhibition/forum,
// visitor information, downloads (brochure, agenda, prospectus) and the post-event archive.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'what' | 'agenda' | 'speakers' | 'venue' | 'downloads' | 'archive', SectionHeading>;
        highlights: Highlight[];
        sessions: { type: string; title: string; day: string; time: string; hall: string; speaker: string | null }[];
        sessionsSample: boolean;
        speakers: SpeakerCard[];
        visitor: { icon: GlyphName; title: string; text: string }[];
        experiences: { icon: GlyphName; tone: string; title: string; text: string; href: string }[];
        downloads: DownloadItem[];
        archiveEmpty: { title: string; body: string };
    };
}>();
const { t } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);
const tones = ['tone-teal', 'tone-sun', 'tone-sky', 'tone-violet'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" variant="facts" />

    <div ref="root">
        <!-- Countdown + TBC -->
        <section class="in-section">
            <div class="container-site grid items-center gap-5 lg:grid-cols-12">
                <div class="in-card tone-teal is-washed in-pad lg:col-span-7" data-reveal="fade">
                    <p class="in-meta">{{ summit.dateConfirmed ? 'Countdown to the Grand Summit' : 'When' }}</p>
                    <Countdown v-if="summit.dateConfirmed" class="mt-3" :target="summit.startsAt" />
                    <p v-else class="in-h2 mt-2 normal-case!">Late November {{ summit.edition }}</p>
                    <p v-if="!summit.dateConfirmed" class="mt-2 text-ink-2">A live countdown starts here as soon as the date is confirmed.</p>
                </div>
                <TbcNotice class="lg:col-span-5" />
            </div>
        </section>

        <!-- Highlights photo grid -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.what" />
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="h in content.highlights" :key="h.title" class="in-card is-solid overflow-hidden" data-reveal="fade">
                        <span class="in-media aspect-[16/9]"><img :src="h.image ?? undefined" alt="" loading="lazy" /></span>
                        <div class="p-5">
                            <h3 class="in-h3 text-lg!">{{ h.title }}</h3>
                            <p class="mt-1.5 text-[0.9375rem] text-ink-2">{{ h.text }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Awards, exhibition, forum -->
        <section class="in-section">
            <div class="container-site">
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="x in content.experiences" :key="x.title" data-reveal="fade"><TintCard :title="x.title" :text="x.text" :icon="x.icon" :tone="x.tone" :href="x.href" label="Learn more" /></li>
                </ul>
            </div>
        </section>

        <!-- Agenda preview -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.agenda"><InBtn href="/agenda" tone="ghost">Full agenda</InBtn></SectionHead>
                <ol class="grid gap-3">
                    <li v-for="(s, i) in content.sessions" :key="s.title" class="in-card is-solid flex-col gap-3 p-5 sm:flex-row! sm:items-center sm:gap-6" :class="tones[i % 4]" data-reveal="fade">
                        <div class="shrink-0 sm:w-40">
                            <p class="font-bold text-night">{{ s.day }}</p>
                            <p class="in-meta">{{ s.time }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="in-pill">{{ s.type }}</span>
                            <h3 class="in-h3 mt-2 text-lg!">{{ s.title }}</h3>
                        </div>
                        <p class="in-meta shrink-0">{{ s.hall }} · {{ s.speaker ?? t('sections.speakerTba') }}</p>
                    </li>
                </ol>
                <SampleRibbon v-if="content.sessionsSample" class="mt-4" />
            </div>
        </section>

        <!-- Speakers -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.speakers"><InBtn href="/speakers" tone="ghost">All speakers</InBtn></SectionHead>
                <ul class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <li v-for="p in content.speakers" :key="p.slug" data-reveal="fade"><PersonCard :person="p" /></li>
                </ul>
            </div>
        </section>

        <!-- Venue + visitor information -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <SectionHead :heading="content.headings.venue" />
                    <div class="in-media is-round grid aspect-[4/3] place-items-center" data-reveal="fade">
                        <div class="flex flex-col items-center gap-3 p-6 text-center">
                            <span class="in-disc tone-teal w-16!"><Glyph name="map" /></span>
                            <p class="font-bold text-night">{{ summit.venue || 'Venue to be confirmed' }}</p>
                            <p class="text-sm text-ink-2">The location map appears here when the venue is announced.</p>
                        </div>
                    </div>
                </div>
                <ul class="grid content-start gap-4 sm:grid-cols-2 lg:col-span-7 lg:pt-24">
                    <li v-for="(v, i) in content.visitor" :key="v.title" data-reveal="fade"><TintCard :title="v.title" :text="v.text" :icon="v.icon" :tone="tones[i]" /></li>
                </ul>
            </div>
        </section>

        <!-- Downloads -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.downloads" />
                <ul class="grid gap-4 md:grid-cols-3">
                    <li v-for="(d, i) in content.downloads" :key="d.kind" data-reveal="fade"><DownloadCard :item="d" :tone="tones[i]" /></li>
                </ul>
            </div>
        </section>

        <!-- Post-event archive -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.archive" />
                <EmptyState level="h3" icon="layers" :title="content.archiveEmpty.title" :body="content.archiveEmpty.body"><InBtn href="/media" tone="ghost">Programme gallery</InBtn></EmptyState>
            </div>
        </section>
    </div>

    <GetInvolved />
</template>
