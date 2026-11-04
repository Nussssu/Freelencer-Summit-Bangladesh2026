<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AwardsCta from '@/components/awards/AwardsCta.vue';
import NomineeCard from '@/components/awards/NomineeCard.vue';
import WinnerCard from '@/components/awards/WinnerCard.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import Glyph from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import Accordion from '@/components/ui/Accordion.vue';
import Icon from '@/components/ui/Icon.vue';
import { ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { AwardsContent, InnerPage } from '@/types/inner';

// Brief §9: dedicated awards landing: overview, categories, a nomination flow separate from registration, statuses,
// shortlist and winners archive, guidelines and FAQ.
const props = defineProps<{ page: InnerPage; content: AwardsContent }>();
const root = ref<HTMLElement>();
useReveal(root);

// Review statuses light up one after another as the row scrolls through the view (and go back on the way up);
// under reduced motion they are all lit from the start.
const statusList = ref<HTMLElement>();
const statusesOn = ref(0);
useGsap(root, (reduced) => {
    const count = props.content.awards.statuses.length;
    if (reduced) {
        statusesOn.value = count;
        return;
    }
    ScrollTrigger.create({
        trigger: statusList.value,
        start: 'top 85%',
        end: 'top 35%',
        onUpdate: (self) => (statusesOn.value = Math.min(count, Math.ceil(self.progress * count))),
        onLeave: () => (statusesOn.value = count),
        onLeaveBack: () => (statusesOn.value = 0),
    });
});
const tones = ['tone-sky', 'tone-rose', 'tone-violet', 'tone-sun', 'tone-mint', 'tone-teal'];
const icons: GlyphName[] = ['rocket', 'star', 'ai', 'pen', 'building', 'cap'];
const factIcons: GlyphName[] = ['pen', 'calendar', 'trophy', 'check'];
const stepIcons: GlyphName[] = ['layers', 'briefcase', 'mail', 'star'];
const statusIcons: GlyphName[] = ['doc', 'search', 'people', 'trophy'];
const slug = (s: string) => s.toLowerCase().replace(/&/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <NewsHero id="awards-title" showcase focus="72% 12%" :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />

    <div ref="root">
        <!-- Overview + key facts -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div class="lg:col-span-6">
                    <SectionHead :heading="content.headings.overview" />
                    <div class="-mt-4" data-reveal="fade"><InBtn href="/awards/nominate" tone="gold">Nominate for Awards</InBtn></div>
                </div>
                <dl class="grid gap-3 sm:grid-cols-2 lg:col-span-6">
                    <div v-for="(f, i) in content.facts" :key="f.label" class="in-card is-washed flex-row! items-center gap-4 p-4" :class="tones[i]" data-reveal="fade">
                        <span class="in-disc w-11!"><Glyph :name="factIcons[i]" /></span>
                        <div class="min-w-0"><dt class="in-meta">{{ f.label }}</dt><dd class="font-bold text-night">{{ f.value }}</dd></div>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Categories -->
        <section id="categories" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.categories" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(c, i) in content.awards.categories" :key="c" data-reveal="fade"><TintCard class="p-6! md:p-7!" :title="c" :text="content.categoryTexts[i] ?? content.categoryNote" :icon="icons[i % 6]" :tone="tones[i % 6]" :href="`/awards/nominate?category=${slug(c)}`" label="Nominate" /></li>
                </ul>
            </div>
        </section>

        <!-- How to nominate: numbered step cards joined by dashes, then the review statuses as a connected line -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.steps" center />
                <ol class="grid gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(s, i) in content.steps" :key="s.title" class="relative pt-6" data-reveal="fade">
                        <span class="step-num absolute top-0 left-1/2 z-[1] grid size-12 -translate-x-1/2 place-items-center rounded-full bg-gradient-to-b from-green to-[#06535b] text-lg font-bold text-white shadow-[0_8px_18px_-8px_rgb(8_112_122/0.7)]">{{ String(i + 1).padStart(2, '0') }}</span>
                        <div class="step-card in-card is-solid h-full items-center overflow-hidden px-6 pt-12 pb-8 text-center">
                            <span class="step-shine" aria-hidden="true" />
                            <span class="grid size-20 place-items-center rounded-2xl bg-[#f6cf6a]/25 text-[#b07a06]"><Glyph :name="stepIcons[i]" class="size-9" /></span>
                            <h3 class="mt-5 text-xl font-bold text-night">{{ s.title }}</h3>
                            <p class="mt-2 text-ink-2">{{ s.text }}</p>
                        </div>
                        <span v-if="i < content.steps.length - 1" class="absolute top-1/2 -right-8 hidden w-8 border-t-2 border-dashed border-green/60 lg:block" aria-hidden="true" />
                    </li>
                </ol>

                <div class="mt-12 grid gap-8 rounded-3xl border border-white/80 bg-white/60 p-6 shadow-[0_30px_70px_-50px_rgb(8_58_64/0.4)] sm:p-10 lg:grid-cols-12 lg:items-center" data-reveal="fade">
                    <div class="lg:col-span-4 lg:border-r lg:border-night/15 lg:pr-10">
                        <h3 class="text-2xl font-bold text-night md:text-3xl">{{ content.headings.statuses.title }}</h3>
                    </div>
                    <ol ref="statusList" class="grid grid-cols-2 gap-y-7 sm:grid-cols-4 lg:col-span-8">
                        <li v-for="(st, i) in content.awards.statuses" :key="st" class="status group relative flex flex-col items-center text-center" :class="[i === content.awards.statuses.length - 1 && 'is-final', i < statusesOn && 'is-on']">
                            <span v-if="i < content.awards.statuses.length - 1" class="absolute top-7 left-[calc(50%+2.25rem)] hidden h-0.5 w-[calc(100%-4.5rem)] overflow-hidden rounded-full bg-night/10 sm:block" aria-hidden="true">
                                <span
                                    class="status-fill block h-full origin-left"
                                    :class="[i === content.awards.statuses.length - 2 ? 'bg-gradient-to-r from-green to-gold' : 'bg-green', i + 1 < statusesOn && 'is-on']"
                                />
                            </span>
                            <span class="status-disc relative grid size-14 place-items-center rounded-full bg-white">
                                <Glyph :name="statusIcons[i]" class="size-6" />
                                <span class="status-num absolute -top-1 -right-1 grid size-5 place-items-center rounded-full text-[0.6875rem] font-bold text-white">{{ i + 1 }}</span>
                            </span>
                            <span class="mt-3 text-[0.9375rem] font-semibold text-night">{{ st }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- Shortlisted nominees: the first four cards under a small "All Categories" label; the full list lives on /awards/nominees -->
        <section id="shortlist" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.shortlist" center />
                <template v-if="content.shortlist.length">
                    <p class="mb-3 text-sm text-ink-2" data-reveal="fade">All Categories</p>
                    <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <li v-for="n in content.shortlist.slice(0, 4)" :key="n.name" data-reveal="fade"><NomineeCard :nominee="n" /></li>
                    </ul>
                    <div class="mt-12 flex justify-center" data-reveal="fade">
                        <Link href="/awards/nominees" class="link-u t-label inline-flex min-h-11 items-center gap-2 text-night">View All <Icon name="arrow" :size="16" /></Link>
                    </div>
                </template>
                <EmptyState v-else level="h3" icon="star" tone="tone-sky" :title="content.shortlistEmpty.title" :body="content.shortlistEmpty.body" />
            </div>
        </section>

        <!-- Featured winner spotlights: the current winners side by side; the full archive lives on /awards/winners -->
        <section id="winners" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.winners" center />
                <template v-if="content.winners.length">
                    <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <li v-for="w in content.winners" :key="w.name" data-reveal="fade"><WinnerCard :winner="w" /></li>
                    </ul>
                    <div class="mt-12 flex justify-center" data-reveal="fade">
                        <Link href="/awards/winners" class="link-u t-label inline-flex min-h-11 items-center gap-2 text-night">View All <Icon name="arrow" :size="16" /></Link>
                    </div>
                </template>
                <EmptyState v-else level="h3" icon="trophy" tone="tone-sun" :title="content.winnersEmpty.title" :body="content.winnersEmpty.body" />
            </div>
        </section>

        <AwardsCta :heading="content.headings.cta" />

        <!-- Guidelines + FAQ -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <SectionHead :heading="content.headings.faq" />
                    <ul class="space-y-4">
                        <li v-for="d in content.downloads" :key="d.kind"><DownloadCard :item="d" tone="tone-sun" /></li>
                    </ul>
                    <p class="mt-4 text-sm"><Link href="/contact?topic=awards" class="font-semibold text-green">Ask the awards team</Link></p>
                </div>
                <div class="in-card is-solid in-pad lg:col-span-8" data-reveal="fade"><Accordion :items="content.faq" id-prefix="aw-faq" /></div>
            </div>
        </section>
    </div>

</template>

<style scoped>
/* Review statuses: small ringed icon discs with a step number, joined by a track. Scrolling lights each disc (and the
   track up to it) in turn; hover fills a disc too. The final status (Winner) is gold */
.status {
    --c: var(--green);
}
.status.is-final {
    --c: #c98a10;
}
.status-disc {
    color: var(--c);
    box-shadow:
        inset 0 0 0 1.5px color-mix(in srgb, var(--c) 45%, transparent),
        0 10px 22px -14px color-mix(in srgb, var(--c) 70%, transparent);
    transition:
        background-color 0.3s ease,
        color 0.3s ease,
        transform 0.3s ease;
}
.status-num {
    background: var(--c);
    box-shadow: 0 0 0 2px #fff;
}
.status.is-on .status-disc,
.status:hover .status-disc {
    background: var(--c);
    color: #fff;
}
.status.is-on .status-disc {
    animation: status-pop 0.5s ease;
}
.status:hover .status-disc {
    transform: translateY(-2px);
}
@keyframes status-pop {
    50% {
        transform: scale(1.12);
    }
}
.status-fill {
    transform: scaleX(0);
    transition: transform 0.5s ease;
}
.status-fill.is-on {
    transform: scaleX(1);
}
@media (prefers-reduced-motion: reduce) {
    .status-disc,
    .status-fill {
        transition: none;
    }
    .status.is-on .status-disc {
        animation: none;
    }
    .status:hover .status-disc {
        transform: none;
    }
}
/* How to nominate: on hover the card lifts, its number pops and a light sweep flashes across it once */
.step-card {
    transition:
        transform 0.35s var(--ease-hover, ease),
        box-shadow 0.35s var(--ease-hover, ease);
}
.step-shine {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(110deg, transparent 35%, rgb(255 255 255 / 0.85) 50%, transparent 65%);
    transform: translateX(-120%);
}
.step-num {
    transition: transform 0.35s var(--ease-hover, ease);
}
li:hover > .step-card {
    transform: translateY(-6px);
    box-shadow: 0 28px 50px -30px rgb(8 58 64 / 0.45);
}
li:hover > .step-card .step-shine {
    transform: translateX(120%);
    transition: transform 0.8s ease;
}
li:hover > .step-num {
    transform: translateY(-4px) scale(1.08);
}
@media (prefers-reduced-motion: reduce) {
    .step-card,
    .step-num,
    .step-shine {
        transition: none !important;
    }
    li:hover > .step-card,
    li:hover > .step-num {
        transform: none;
    }
}
</style>
