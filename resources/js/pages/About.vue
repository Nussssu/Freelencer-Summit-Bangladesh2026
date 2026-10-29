<script setup lang="ts">
import AboutAudiences from '@/components/about/AboutAudiences.vue';
import AboutClosing from '@/components/about/AboutClosing.vue';
import AboutHero from '@/components/about/AboutHero.vue';
import AboutHighlights from '@/components/about/AboutHighlights.vue';
import AboutObjectives from '@/components/about/AboutObjectives.vue';
import AboutOrganisers from '@/components/about/AboutOrganisers.vue';
import AboutStatement from '@/components/about/AboutStatement.vue';
import AboutStrands from '@/components/about/AboutStrands.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { AboutContent, InnerPage } from '@/types/inner';

// Brief §1–4: positioning, objectives, the four implementation strands, audiences, summit highlights, organisers.
// Page-local sections live in components/about; the 3D canvas stays off (inner-page rule).
defineProps<{ page: InnerPage; content: AboutContent }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" image="/media/photos/highlight-keynote.jpg" />
    <div class="on-light">
        <AboutStatement :heading="content.headings.why" :about="content.about" />
        <AboutObjectives :heading="content.headings.objectives" :objectives="content.objectives" />
        <AboutStrands :heading="content.headings.phases" :phases="content.phases" />
        <AboutAudiences :heading="content.headings.audiences" :audiences="content.audiences" />
        <AboutHighlights :heading="content.headings.highlights" :highlights="content.highlights" />
        <AboutOrganisers :heading="content.headings.organisers" />
    </div>
    <AboutClosing :heading="content.headings.cta" />
</template>
