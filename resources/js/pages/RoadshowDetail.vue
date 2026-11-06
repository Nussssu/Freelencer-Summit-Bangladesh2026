<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AboutHero from '@/components/about/AboutHero.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import SpeakerDialog from '@/components/speakers/SpeakerDialog.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, ProgramCard } from '@/types/inner';

// A district roadshow's (and a campus programme's and divisional summit's) page: the About-style hero over a landmark of the district, then "About this programme" and the
// organiser's logo beside the facts card ("Registration closed" pill on top once that applies, venue links to Google Maps,
// Register goes to summit registration), photos (when Settings › Programmes turns them on) and more programmes.
const props = defineProps<{
    page: InnerPage;
    content: {
        program: ProgramCard & {
            description: string | null;
            speakers: { slug: string; name: string; designation: string | null; organisation: string | null; role: string | null; photo: string | null; expertise: string[] }[];
            media: { kind: string; src: string | null; youtubeId: string | null; caption: string | null }[];
        };
        typeLabel: string;
        facts: { icon: GlyphName; label: string; value: string }[];
        related: ProgramCard[];
        backHref: string;
        backLabel: string;
        photoCredit?: { title: string; author: string; license: string; source: string } | null;
        showMedia: boolean;
        registrationClosed: boolean;
        partner: { name: string; logo: string | null } | null;
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
// Index of the speaker shown in the popup (null = closed).
const speaker = ref<number | null>(null);

const date = (iso: string | null) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.toBeConfirmed'));
// Venue → Google Maps search for the venue in its district.
const venue = props.content.program.location;
const maps = venue ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent([venue, props.content.program.district, 'Bangladesh'].filter(Boolean).join(', '))}` : null;
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <div class="relative">
        <AboutHero :page="page" />
        <a v-if="content.photoCredit" :href="content.photoCredit.source" target="_blank" rel="noopener" class="absolute right-3 bottom-2 max-w-[80%] truncate text-[0.6875rem] text-white/60 transition-colors hover:text-white">
            Photo: {{ content.photoCredit.author }}, {{ content.photoCredit.license }}, via Wikimedia Commons
        </a>
    </div>

    <div ref="root">
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:gap-14">
                <div class="min-w-0 lg:col-span-8">
                    <h2 class="in-h3 text-2xl!">About this programme</h2>
                    <div v-if="content.program.description" class="in-prose mt-4" v-html="content.program.description" />
                    <p v-else class="in-body mt-4">The full programme, speakers and mentors are published here as they are confirmed.</p>

                    <div v-if="content.partner" class="mt-12">
                        <h2 class="in-h3 text-2xl!">Organiser / Partner</h2>
                        <figure class="mt-5 flex items-center gap-5">
                            <img v-if="content.partner.logo" :src="content.partner.logo" :alt="`${content.partner.name} logo`" class="size-28 shrink-0 rounded-2xl shadow-[0_18px_36px_-24px_rgb(7_20_17/0.5)]" loading="lazy" />
                            <span v-else class="in-disc w-28! shrink-0 tone-teal"><Glyph name="handshake" class="size-10" /></span>
                            <figcaption class="text-lg font-semibold text-ink-2">{{ content.partner.name }}</figcaption>
                        </figure>
                    </div>

                    <!-- Speakers, under the organiser: a click opens the speaker popup -->
                    <div class="mt-12">
                        <h2 class="in-h3 text-2xl!">Speakers and mentors</h2>
                        <ul v-if="content.program.speakers.length" class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                            <li v-for="(s, i) in content.program.speakers" :key="s.slug">
                                <button type="button" class="group block w-full overflow-hidden rounded-xl bg-white text-left ring-1 ring-night/8 transition-shadow duration-300 hover:shadow-[0_18px_36px_-22px_rgb(7_20_17/0.45)] focus-visible:outline-2 focus-visible:outline-green" :aria-label="`${s.name}: view details`" @click="speaker = i">
                                    <span class="block aspect-[4/5] overflow-hidden bg-mist"><img v-if="s.photo" :src="s.photo" :alt="s.name" loading="lazy" class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-105" /></span>
                                    <span class="block p-4">
                                        <span v-if="s.role" class="in-meta block">{{ s.role }}</span>
                                        <span class="mt-0.5 block font-extrabold text-night group-hover:text-green">{{ s.name }}</span>
                                        <span v-if="s.designation || s.organisation" class="mt-1 block text-sm text-ink-2">{{ [s.designation, s.organisation].filter(Boolean).join(', ') }}</span>
                                    </span>
                                </button>
                            </li>
                        </ul>
                        <p v-else class="in-body mt-4">Speakers and mentors are announced here as they are confirmed.</p>
                        <SpeakerDialog v-model:index="speaker" :people="content.program.speakers" />
                    </div>

                    <div v-if="content.showMedia" class="mt-12">
                        <h2 class="in-h3 text-2xl!">Photos and videos</h2>
                        <ul v-if="content.program.media.length" class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                            <li v-for="(m, i) in content.program.media" :key="i" class="in-media is-round aspect-[4/3]">
                                <img v-if="m.src" :src="m.src" :alt="m.caption ?? ''" loading="lazy" />
                            </li>
                        </ul>
                        <EmptyState v-else class="mt-5" level="h3" icon="image" title="Gallery after the programme" body="Photos and videos are added here once the programme has taken place." />
                    </div>
                </div>

                <aside class="lg:col-span-4" aria-label="Programme details">
                    <div class="space-y-5 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
                        <div class="in-card is-solid in-pad space-y-5">
                            <span v-if="content.registrationClosed" class="in-pill tone-rose self-start"><Glyph name="clock" class="size-4" />Registration closed</span>
                            <dl class="space-y-4">
                                <div v-for="f in content.facts" :key="f.label" class="flex items-center gap-3 tone-teal">
                                    <span class="in-disc w-11!"><Glyph :name="f.icon" /></span>
                                    <div class="min-w-0">
                                        <dt class="in-meta">{{ f.label }}</dt>
                                        <dd class="font-bold text-night">
                                            <a v-if="f.icon === 'pin' && maps" :href="maps" target="_blank" rel="noopener" class="group inline-flex items-start gap-1.5 transition-colors hover:text-green" :aria-label="`${f.value}: open in Google Maps`">
                                                {{ f.value }}
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-green transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" /></svg>
                                            </a>
                                            <template v-else>{{ f.value }}</template>
                                        </dd>
                                    </div>
                                </div>
                            </dl>
                            <div class="flex flex-wrap gap-3 border-t border-night/8 pt-5">
                                <InBtn v-if="!content.registrationClosed" href="/register" tone="gold">Register</InBtn>
                                <ShareButton :title="content.program.title" />
                            </div>
                            <Link :href="content.backHref" class="in-link">← {{ content.backLabel }}</Link>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section v-if="content.related.length" class="in-section">
            <div class="container-site">
                <SectionHead :heading="{ title: 'More programmes', emphasis: 'programmes' }" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="r in content.related.slice(0, 3)" :key="r.slug"><InfoCard :title="r.title" :href="r.href" :image="r.image ?? '/media/photos/gallery-09.jpg'" :meta="[date(r.startsAt), r.location]" :status="{ value: r.status, label: r.statusLabel }" /></li>
                </ul>
            </div>
        </section>
    </div>
</template>
