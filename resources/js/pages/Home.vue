<script setup lang="ts">
import { computed } from 'vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import AgendaPreview from '@/components/sections/AgendaPreview.vue';
import AwardsSpotlight from '@/components/sections/AwardsSpotlight.vue';
import ExpoSponsor from '@/components/sections/ExpoSponsor.vue';
import FinalCta from '@/components/sections/FinalCta.vue';
import ForumSection from '@/components/sections/ForumSection.vue';
import MediaGallery from '@/components/sections/MediaGallery.vue';
import NewsList from '@/components/sections/NewsList.vue';
import PartnersWall from '@/components/sections/PartnersWall.vue';
import StoriesSection from '@/components/sections/StoriesSection.vue';
import HeroSection from '@/components/sections/HeroSection.vue';
import HighlightsSection from '@/components/sections/HighlightsSection.vue';
import JourneySection from '@/components/sections/JourneySection.vue';
import SpeakersSection from '@/components/sections/SpeakersSection.vue';
import TrustStrip from '@/components/sections/TrustStrip.vue';
import WhatIsSection from '@/components/sections/WhatIsSection.vue';
import WhyAttend from '@/components/sections/WhyAttend.vue';
import { useShared } from '@/composables/useShared';
import type { HomeContent } from '@/types/content';

// Exactly the client's 17 homepage sections, in brief order (17 = footer in the persistent layout).
defineProps<{ content: HomeContent }>();
const { summit } = useShared();
const title = computed(() => `Freelancer Summit Bangladesh ${summit.value.edition} · Grand Summit, Dhaka`);
const description = computed(
    () => `The national flagship summit for Bangladeshi freelancers: ${summit.value.campuses} campus programmes, ${summit.value.districts} district roadshows and ${summit.value.divisions} divisional summits leading to the Grand Summit in Dhaka, late November ${summit.value.edition}.`,
);
</script>

<template>
    <SeoHead :title="title" :description="description" path="/" />
    <HeroSection :hero="content.hero" />
    <TrustStrip :trust="content.trust" />
    <WhatIsSection :about="content.about" />
    <JourneySection :journey="content.journey" />
    <WhyAttend :audiences="content.audiences" :heading="content.headings.why" />
    <HighlightsSection :highlights="content.highlights" :heading="content.headings.highlights" />
    <SpeakersSection :speakers="content.speakers" :heading="content.headings.speakers" />
    <AgendaPreview :agenda="content.agenda" :heading="content.headings.agenda" />
    <AwardsSpotlight :awards="content.awards" />
    <ExpoSponsor :exhibition="content.exhibition" :sponsorship="content.sponsorship" :headings="{ exhibition: content.headings.exhibition, sponsorship: content.headings.sponsorship }" />
    <ForumSection :forum="content.forum" :heading="content.headings.forum" />
    <StoriesSection :stories="content.stories" :heading="content.headings.stories" />
    <PartnersWall :partners="content.partners" :heading="content.headings.partners" />
    <NewsList :news="content.news" :heading="content.headings.news" />
    <MediaGallery :media="content.media" :heading="content.headings.media" />
    <FinalCta :final-cta="content.finalCta" />
</template>
