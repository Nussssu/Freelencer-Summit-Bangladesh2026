<script setup lang="ts">
import AboutClosing from '@/components/about/AboutClosing.vue';
import AboutGoals from '@/components/about/AboutGoals.vue';
import AboutHero from '@/components/about/AboutHero.vue';
import AboutIntro from '@/components/about/AboutIntro.vue';
import AboutNews from '@/components/about/AboutNews.vue';
import AboutOrganisers from '@/components/about/AboutOrganisers.vue';
import AboutOverview from '@/components/about/AboutOverview.vue';
import AboutPillars from '@/components/about/AboutPillars.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { AboutContent, InnerPage } from '@/types/inner';

// The client's About mock-up, top to bottom: short title hero, the summit's name and positioning, overview, purpose and vision, key objectives,
// latest news marquee, organisers, closing band. Page-local sections live in components/about; the 3D canvas stays off (inner-page rule).
defineProps<{ page: InnerPage; content: AboutContent }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />
    <div class="ground on-light">
        <AboutIntro :intro="content.intro" />
        <AboutOverview :heading="content.headings.overview" :overview="content.overview" />
        <AboutPillars :heading="content.headings.pillars" :pillars="content.pillars" />
        <AboutGoals :heading="content.headings.goals" :goals="content.goals" />
        <AboutNews :heading="content.headings.news" :news="content.latestNews" />
        <AboutOrganisers :heading="content.headings.organisers" :organisers="content.organisers" />
        <AboutClosing :heading="content.headings.cta" :page="page" />
    </div>
</template>

<style scoped>
/*
 * One ground under every section below the hero: ice blue with soft teal and summit-gold glows that alternate sides
 * down the page. Fixed-size glows, so they keep their shape however tall the page grows, and no seams between sections.
 */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
</style>
