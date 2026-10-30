<script setup lang="ts">
import { ref } from 'vue';
import NjDivisions from '@/components/journey-page/NjDivisions.vue';
import NjProgress from '@/components/journey-page/NjProgress.vue';
import NjRoute from '@/components/journey-page/NjRoute.vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import PageHero from '@/components/inner/PageHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import JourneyDirectory from '@/components/sections/journey/JourneyDirectory.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, JourneyContent } from '@/types/inner';

// Brief §15: progress counters, map + timeline from grassroots to Dhaka, 8 division summits, activation directory.
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

    <div class="on-light">
        <NjProgress :heading="content.headings.progress" :counters="content.journey.counters" :reel="content.reel" />
        <NjRoute :heading="content.headings.route" :steps="content.journey.steps" :beacon-label="content.journey.beaconLabel" />
        <NjDivisions :heading="content.headings.divisions" :summits="content.divisionSummits" :href="content.journey.steps[2].href" />

        <section class="border-t border-line-light bg-paper">
            <JourneyDirectory :directory="content.journey.directory" :steps="content.journey.steps" />
        </section>

        <!-- What each activity page carries (brief §15) -->
        <section ref="root" class="section bg-snow" aria-labelledby="nj-includes">
            <div class="container-site grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4"><SectionHead id="nj-includes" :heading="content.headings.includes" class="lg:sticky lg:top-[calc(var(--header-h)+2rem)]" /></div>
                <dl class="grid border-t border-line-light sm:grid-cols-2 lg:col-span-8">
                    <div v-for="x in content.includes" :key="x.title" class="border-b border-line-light py-7 sm:odd:border-r sm:odd:pr-8 sm:even:pl-8" data-reveal="fade">
                        <dt class="flex items-center gap-3 text-xl font-bold"><span class="h-px w-5 bg-gold" aria-hidden="true" />{{ x.title }}</dt>
                        <dd class="mt-2 max-w-sm text-ink-2">{{ x.text }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink href="/register">{{ page.hero.actions?.[0]?.label ?? 'Register for Summit' }}</CtaLink>
        <CtaLink :href="content.journey.steps[0].href" tone="outline">{{ content.journey.steps[0].tag }}</CtaLink>
    </CtaBand>
</template>
