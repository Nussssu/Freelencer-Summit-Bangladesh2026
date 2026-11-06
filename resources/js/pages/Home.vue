<script setup lang="ts">
import { computed, ref } from 'vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import AgendaPreview from '@/components/sections/AgendaPreview.vue';
import AwardsIntro from '@/components/sections/AwardsIntro.vue';
import ExpoIntro from '@/components/sections/ExpoIntro.vue';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import ForumIntro from '@/components/sections/ForumIntro.vue';
import MediaGallery from '@/components/sections/MediaGallery.vue';
import LatestNews from '@/components/sections/LatestNews.vue';
import PartnersWall from '@/components/sections/PartnersWall.vue';
import StoriesIntro from '@/components/sections/StoriesIntro.vue';
import HeroSection from '@/components/sections/HeroSection.vue';
import JourneySection from '@/components/sections/JourneySection.vue';
import NjFinder from '@/components/journey-page/NjFinder.vue';
import JourneyCounters from '@/components/sections/journey/JourneyCounters.vue';
import SpeakersSection from '@/components/sections/SpeakersSection.vue';
import TrustStrip from '@/components/sections/TrustStrip.vue';
import WhatIsSection from '@/components/sections/WhatIsSection.vue';
import { gsap, ScrollTrigger, useGsap } from '@/composables/motion';
import { useShared } from '@/composables/useShared';
import type { HomeContent } from '@/types/content';

// Exactly the client's 17 homepage sections, in brief order (17 = footer in the persistent layout).
defineProps<{ content: HomeContent }>();
const { summit } = useShared();
const programmeFinderHeading = { title: 'Find a programme near you.', emphasis: 'near you.', intro: null };
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

// Page 5 (exhibition & sponsorship) arrives the same way over the awards intro: the awards block is held as soon as it's
// fully on screen (its bottom at the bottom) and stays put until the next page has covered it, and the next page paints
// the site blend so nothing shows through. The next page sits a dwell lower (hidden behind the held block), so the settled
// trophy gets a moment on screen before anything rises. refreshPriority -1: after the pins above.
const awardsHold = ref<HTMLElement>();
const page5 = ref<HTMLElement>();
useGsap(page5, (reduced) => {
    const el = page5.value;
    const held = awardsHold.value;
    if (reduced || !el || !held) return;
    const dwell = () => Math.round(innerHeight * 0.6);
    const setDwell = () => gsap.set(el, { marginTop: dwell() });
    setDwell();
    ScrollTrigger.addEventListener('refreshInit', setDwell);
    ScrollTrigger.create({
        trigger: held,
        start: 'bottom bottom',
        end: () => `+=${dwell() + Math.min(held.offsetHeight, innerHeight)}`,
        pin: true,
        pinSpacing: false,
        invalidateOnRefresh: true,
        refreshPriority: -1,
    });
    const st = { trigger: el, start: 'top bottom', end: 'top top', scrub: true, refreshPriority: -1 };
    gsap.fromTo(
        el,
        { clipPath: 'inset(0% 4% 0% 4% round 2.5rem 2.5rem 0rem 0rem)' },
        { clipPath: 'inset(0% 0% 0% 0% round 0rem 0rem 0rem 0rem)', ease: 'power2.out', scrollTrigger: { ...st, onLeave: () => gsap.set(el, { clipPath: 'none' }), onEnterBack: () => gsap.set(el, { clipPath: '' }) } },
    );
    gsap.fromTo(el.querySelector('.container-site')!.children, { y: 48, opacity: 0.2 }, { y: 0, opacity: 1, ease: 'power2.out', stagger: 0.1, scrollTrigger: { ...st, end: 'top 40%' } });
    return () => ScrollTrigger.removeEventListener('refreshInit', setDwell);
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
        <NjFinder class="home-programme-finder" :heading="programmeFinderHeading" :directory="content.journey.directory" />
    </div>
    <SpeakersSection :speakers="content.speakers" :heading="content.headings.speakers" />
    <AgendaPreview :agenda="content.agenda" :heading="content.headings.agenda" />
    <div ref="awardsHold" class="relative z-0"><AwardsIntro /></div>
    <!-- Page 5: exhibition & sponsorship, sliding over the held awards intro (as page 2 over the hero) -->
    <div ref="page5" class="relative z-[2] [background:var(--grad-site)_fixed]"><ExpoIntro /></div>
    <ForumIntro />
    <StoriesIntro :cards="content.storyCards" />
    <PartnersWall :partners="content.partners" :heading="content.headings.partners" />
    <LatestNews :heading="content.headings.news" :news="content.latestNews" />
    <MediaGallery :media="content.media" :heading="content.headings.media" />
    <GetInvolved />
</template>

<style scoped>
.home-programme-finder {
    margin-top: var(--section-gap);
    padding-block: 0 !important;
}
</style>
