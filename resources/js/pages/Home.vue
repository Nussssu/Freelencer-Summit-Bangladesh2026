<script setup lang="ts">
import { computed, ref } from 'vue';
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
import JourneyCounters from '@/components/sections/journey/JourneyCounters.vue';
import JourneyDirectory from '@/components/sections/journey/JourneyDirectory.vue';
import SpeakersSection from '@/components/sections/SpeakersSection.vue';
import TrustStrip from '@/components/sections/TrustStrip.vue';
import WhatIsSection from '@/components/sections/WhatIsSection.vue';
import WhyAttend from '@/components/sections/WhyAttend.vue';
import { gsap, useGsap } from '@/composables/motion';
import { useShared } from '@/composables/useShared';
import type { HomeContent } from '@/types/content';

// Exactly the client's 17 homepage sections, in brief order (17 = footer in the persistent layout).
defineProps<{ content: HomeContent }>();
const { summit } = useShared();
const title = computed(() => `Freelancer Summit Bangladesh ${summit.value.edition} · Grand Summit, Dhaka`);
const description = computed(
    () => `The national flagship summit for Bangladeshi freelancers: ${summit.value.campuses} campus programmes, ${summit.value.districts} district roadshows and ${summit.value.divisions} divisional summits leading to the Grand Summit in Dhaka, late November ${summit.value.edition}.`,
);

// Page 2 (trust strip + "What is") slides over the pinned hero. Section reveal: it rises with rounded top corners
// and inset sides that open to full width as it reaches the top, while the strip's content lifts in. The clip is
// dropped once open, so it can't cut off the "What is" section when that pins later.
const page2 = ref<HTMLElement>();
useGsap(page2, (reduced) => {
    const el = page2.value;
    if (reduced || !el) return;
    const st = { trigger: el, start: 'top bottom', end: 'top top', scrub: true };
    gsap.fromTo(
        el,
        { clipPath: 'inset(0% 4% 0% 4% round 2.5rem 2.5rem 0rem 0rem)' },
        { clipPath: 'inset(0% 0% 0% 0% round 0rem 0rem 0rem 0rem)', ease: 'power2.out', scrollTrigger: { ...st, onLeave: () => gsap.set(el, { clipPath: 'none' }) } },
    );
    gsap.fromTo('[data-section="2"] > *', { y: 48, opacity: 0.2 }, { y: 0, opacity: 1, ease: 'power2.out', stagger: 0.1, scrollTrigger: { ...st, end: 'top 40%' } });
});

// Page 3 (campaign progress + why attend) arrives the same way over the held journey map. It paints the site blend
// itself so the map doesn't show through. refreshPriority -1: measured after the journey pin above it.
const page3 = ref<HTMLElement>();
useGsap(page3, (reduced) => {
    const el = page3.value;
    if (reduced || !el) return;
    const st = { trigger: el, start: 'top bottom', end: 'top top', scrub: true, refreshPriority: -1 };
    gsap.fromTo(
        el,
        { clipPath: 'inset(0% 4% 0% 4% round 2.5rem 2.5rem 0rem 0rem)' },
        { clipPath: 'inset(0% 0% 0% 0% round 0rem 0rem 0rem 0rem)', ease: 'power2.out', scrollTrigger: { ...st, onLeave: () => gsap.set(el, { clipPath: 'none' }), onEnterBack: () => gsap.set(el, { clipPath: '' }) } },
    );
    gsap.fromTo(el.querySelector('.on-light > .container-site')!.children, { y: 48, opacity: 0.2 }, { y: 0, opacity: 1, ease: 'power2.out', stagger: 0.1, scrollTrigger: { ...st, end: 'top 40%' } });
});
</script>

<template>
    <SeoHead :title="title" :description="description" path="/" />
    <HeroSection :hero="content.hero" />
    <div ref="page2" class="relative z-[1]">
        <TrustStrip :trust="content.trust" />
        <WhatIsSection :about="content.about" />
    </div>
    <JourneySection :journey="content.journey" />
    <!-- Page 3: campaign progress + why attend, sliding over the held journey map (as page 2 over the hero) -->
    <div ref="page3" class="relative z-[1] mt-[calc(-1*var(--journey-hold,0px))] [background:var(--grad-site)_fixed]">
        <div class="on-light flow relative">
            <JourneyCounters :counters="content.journey.counters" />
        </div>
        <WhyAttend :audiences="content.audiences" :heading="content.headings.why" />
    </div>
    <div class="on-light flow relative z-[1]">
        <JourneyDirectory :directory="content.journey.directory" :steps="content.journey.steps" />
    </div>
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
