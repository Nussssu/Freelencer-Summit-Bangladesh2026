<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import PageHero from '@/components/inner/PageHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Accordion from '@/components/ui/Accordion.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { AwardsContent, InnerPage } from '@/types/inner';

// Brief §9: dedicated awards landing with its own identity (the one deep-teal band), categories, a nomination flow
// kept separate from summit registration, application statuses, shortlist and winners archive, guidelines, FAQ.
defineProps<{ page: InnerPage; content: AwardsContent }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <PageHero :eyebrow="page.hero.eyebrow" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" tone="light" :crumbs="page.crumbs">
        <template #actions>
            <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
        </template>
    </PageHero>

    <div ref="root">
        <!-- Overview: the awards' own identity band (deep teal + gold) -->
        <section class="grain section relative bg-delta text-paper" aria-labelledby="aw-overview">
            <div class="container-site grid gap-12 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <p class="t-label text-gold" data-reveal="fade">{{ content.headings.overview.eyebrow }}</p>
                    <SplitHeading id="aw-overview" :text="content.headings.overview.title" :emphasis="content.headings.overview.emphasis ?? undefined" emphasis-class="text-gold" class="t-h2-xl mt-3" />
                    <div class="stitch mt-8 max-w-xs" data-reveal="stitch" aria-hidden="true" />
                    <p class="mt-8 max-w-xl text-lg text-paper/85" data-reveal="fade">{{ content.awards.eligibility }}</p>
                </div>
                <dl class="grid grid-cols-2 border-t border-line-dark lg:col-span-5">
                    <div v-for="f in content.facts" :key="f.label" class="border-b border-line-dark py-5 pr-4 odd:border-r [&:nth-child(even)]:pl-5" data-reveal="fade">
                        <dt class="t-label text-paper/60">{{ f.label }}</dt>
                        <dd class="mt-2 text-lg font-semibold">{{ f.value }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="on-light">
            <!-- Categories (provisional until the guidelines are final) -->
            <section class="section" aria-labelledby="aw-categories">
                <div class="container-site">
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <SectionHead id="aw-categories" :heading="content.headings.categories" />
                        <CtaLink href="/awards/categories" tone="outline">{{ page.hero.actions[1].label }}</CtaLink>
                    </div>
                    <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <li v-for="(c, i) in content.awards.categories" :key="c" class="card card-hover group relative flex min-h-56 flex-col p-6" data-reveal="fade">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-display text-4xl leading-none font-black text-green">{{ String(i + 1).padStart(2, '0') }}</span>
                                <span v-if="content.awards.categoriesProvisional" class="t-label border border-gold px-2 py-1 text-ink-2">{{ $t('common.provisional') }}</span>
                            </div>
                            <h3 class="mt-auto pt-8 font-display text-3xl leading-none font-black uppercase">
                                <Link href="/awards/categories" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ c }}</Link>
                            </h3>
                            <p class="mt-3 text-sm text-ink-2">{{ content.categoryNote }}</p>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- How to apply + what happens after -->
            <section class="section bg-mist" aria-labelledby="aw-steps">
                <div class="container-site grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <SectionHead id="aw-steps" :heading="content.headings.steps" />
                        <ol class="mt-10 grid gap-6 sm:grid-cols-2">
                            <li v-for="(s, i) in content.steps" :key="s.title" class="card p-6" data-reveal="fade">
                                <p class="t-label text-green">{{ $t('inner.form.step', { n: i + 1, total: content.steps.length }) }}</p>
                                <h3 class="mt-3 text-xl font-semibold">{{ s.title }}</h3>
                                <p class="mt-2 text-ink-2">{{ s.text }}</p>
                            </li>
                        </ol>
                    </div>
                    <div class="lg:col-span-5">
                        <SectionHead :heading="content.headings.statuses" />
                        <ol class="relative mt-10 border-l border-line-light">
                            <li v-for="(st, i) in content.awards.statuses" :key="st" class="relative pb-7 pl-8 last:pb-0" data-reveal="fade">
                                <span class="absolute top-1 -left-[7px] size-3.5 border-2 border-mist" :class="i === content.awards.statuses.length - 1 ? 'bg-gold' : 'bg-green'" aria-hidden="true" />
                                <p class="font-display text-2xl leading-none font-black uppercase">{{ st }}</p>
                            </li>
                        </ol>
                        <CtaLink href="/awards/nominate" class="mt-10">{{ page.hero.actions[0].label }}</CtaLink>
                    </div>
                </div>
            </section>

            <!-- Shortlist + winners archive: honest empty states until results exist -->
            <section class="section" aria-labelledby="aw-results">
                <h2 id="aw-results" class="sr-only">{{ content.headings.shortlist.title }} · {{ content.headings.winners.title }}</h2>
                <div class="container-site grid gap-10 lg:grid-cols-2">
                    <div>
                        <SectionHead :heading="content.headings.shortlist" />
                        <EmptyState class="mt-8" level="h3" :title="content.shortlistEmpty.title" :body="content.shortlistEmpty.body" />
                    </div>
                    <div>
                        <SectionHead :heading="content.headings.winners" />
                        <EmptyState class="mt-8" level="h3" :title="content.winnersEmpty.title" :body="content.winnersEmpty.body" />
                    </div>
                </div>
            </section>

            <!-- Guidelines + FAQ -->
            <section class="section bg-snow" aria-labelledby="aw-faq">
                <div class="container-site grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <SectionHead id="aw-faq" :heading="content.headings.faq" />
                        <ul class="mt-8 space-y-4">
                            <li v-for="d in content.downloads" :key="d.kind"><DownloadCard :item="d" /></li>
                        </ul>
                    </div>
                    <div class="lg:col-span-8"><Accordion :items="content.faq" id-prefix="aw-faq" /></div>
                </div>
            </section>
        </div>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
    </CtaBand>
</template>
