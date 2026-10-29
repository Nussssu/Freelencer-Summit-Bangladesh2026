<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import PageHero from '@/components/inner/PageHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import JourneyCounters from '@/components/sections/journey/JourneyCounters.vue';
import JourneyDirectory from '@/components/sections/journey/JourneyDirectory.vue';
import JourneyMapStages from '@/components/sections/journey/JourneyMapStages.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, JourneyContent } from '@/types/inner';

// Brief §15: progress counters, map + timeline from grassroots to Dhaka, 8 division cards, activation directory.
// The map plays its campus → district → division → Dhaka reveal once in view (no pinning on inner pages).
defineProps<{ page: InnerPage; content: JourneyContent }>();
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

    <div ref="root" class="on-light">
        <!-- Progress counters (CMS-managed, config/summit.php) -->
        <section class="bg-snow pt-[var(--section-y)] pb-6" aria-labelledby="nj-progress">
            <div class="container-site"><SectionHead id="nj-progress" :heading="content.headings.progress" /></div>
            <JourneyCounters :counters="content.journey.counters" class="!pt-8" />
        </section>

        <!-- Map + timeline: campuses → districts → divisions → Dhaka -->
        <section class="section" aria-labelledby="nj-route">
            <div class="container-site grid gap-12 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7">
                    <JourneyMapStages :beacon-label="content.journey.beaconLabel" />
                </div>
                <div class="lg:col-span-5">
                    <SectionHead id="nj-route" :heading="content.headings.route" />
                    <ol class="relative mt-10 border-l border-line-light">
                        <li v-for="(s, i) in content.journey.steps" :key="s.title" class="relative pb-8 pl-8 last:pb-0" data-reveal="fade">
                            <span class="absolute top-1 -left-[7px] size-3.5 border-2 border-paper" :class="i === content.journey.steps.length - 1 ? 'bg-red' : 'bg-green'" aria-hidden="true" />
                            <p class="t-label text-ink-2">{{ String(i + 1).padStart(2, '0') }} · {{ s.tag }}</p>
                            <h3 class="mt-2 font-display text-3xl leading-none font-black uppercase">{{ s.title }}</h3>
                            <p class="mt-2 text-ink-2">{{ s.text }}</p>
                            <Link :href="s.href" class="link-u t-label mt-3 inline-flex min-h-11 items-center gap-2 text-green">{{ s.tag }} <Icon name="arrow" :size="14" /></Link>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- 8 divisional summits -->
        <section class="section bg-mist" aria-labelledby="nj-divisions">
            <div class="container-site">
                <SectionHead id="nj-divisions" :heading="content.headings.divisions" />
                <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(d, i) in content.journey.divisions" :key="d.name" class="card card-hover group relative flex min-h-44 flex-col justify-between p-6" data-reveal="fade">
                        <p class="t-label text-ink-2">{{ String(i + 1).padStart(2, '0') }} · {{ content.journey.steps[2].tag }}</p>
                        <div>
                            <h3 class="font-display text-4xl leading-none font-black uppercase">
                                <Link :href="content.journey.steps[2].href" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ d.name }}</Link>
                            </h3>
                            <p class="t-label mt-3 flex items-center gap-2 text-ink-2"><Icon name="calendar" :size="14" /> {{ $t('inner.tbc.toBeConfirmed') }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Activation directory (sample rows until programmes are published) -->
        <section class="bg-paper">
            <JourneyDirectory :directory="content.journey.directory" :steps="content.journey.steps" />
        </section>

        <!-- What each activity page carries (brief §15) -->
        <section class="section bg-snow" aria-labelledby="nj-includes">
            <div class="container-site grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-5"><SectionHead id="nj-includes" :heading="content.headings.includes" /></div>
                <ul class="grid gap-x-6 sm:grid-cols-2 lg:col-span-7">
                    <li v-for="(x, i) in content.includes" :key="x" class="flex items-center gap-4 border-b border-line-light py-5" data-reveal="fade">
                        <span class="t-label text-green">{{ String(i + 1).padStart(2, '0') }}</span>
                        <span class="text-lg font-semibold">{{ x }}</span>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink href="/register">{{ page.hero.actions[0].label }}</CtaLink>
        <CtaLink :href="content.journey.steps[0].href" tone="outline">{{ content.journey.steps[0].tag }}</CtaLink>
    </CtaBand>
</template>
