<script setup lang="ts">
import { ref } from 'vue';
import AboutClosing from '@/components/about/AboutClosing.vue';
import AboutHero from '@/components/about/AboutHero.vue';
import AboutIntro from '@/components/about/AboutIntro.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Accordion from '@/components/ui/Accordion.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, InnerPage, SectionHeading } from '@/types/inner';

// Brief §12: the forum as a long-term ecosystem: purpose, benefits, eligibility, how to join, community, member portal, FAQ.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'benefits' | 'how' | 'activities' | 'faq' | 'cta', SectionHeading>;
        forum: { intro: string; purpose: string; eligibility: string; portalNote: string };
        intro: AboutContent['intro'];
        benefits: { title: string; text: string; icon: GlyphName; tone: string }[];
        steps: { title: string; text: string }[];
        activities: { icon: GlyphName; title: string; text: string; href?: string }[];
        faq: { q: string; a: string }[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const tones = ['tone-blue', 'tone-teal', 'tone-sun'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root">
        <AboutIntro :intro="content.intro" justify />

        <section id="benefits" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.benefits"><InBtn href="/forum/register" tone="gold">Join the forum</InBtn></SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="b in content.benefits" :key="b.title" data-reveal="fade"><TintCard :title="b.title" :text="b.text" :icon="b.icon" :tone="b.tone" /></li>
                </ul>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.how" />
                <ol class="grid gap-5 md:grid-cols-3">
                    <li v-for="(s, i) in content.steps" :key="s.title" data-reveal="fade"><TintCard :title="s.title" :text="s.text" :number="String(i + 1).padStart(2, '0')" :tone="tones[i]" /></li>
                </ol>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.activities" />
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="(a, i) in content.activities" :key="a.title" data-reveal="fade"><TintCard :title="a.title" :text="a.text" :icon="a.icon" :tone="['tone-teal', 'tone-rose', 'tone-violet'][i]" :href="a.href" :label="a.href ? 'Read stories' : undefined" /></li>
                </ul>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-4 lg:[&_.in-head]:mb-0"><SectionHead :heading="content.headings.faq" /></div>
                <div class="in-card is-solid in-pad lg:col-span-8" data-reveal="fade"><Accordion :items="content.faq" id-prefix="forum-faq" /></div>
            </div>
        </section>
    </div>

    <div class="pt-8 md:pt-12">
        <AboutClosing :heading="content.headings.cta" :page="page">
            <CtaLink href="/forum/register" class="!px-7 !py-4">Join the forum</CtaLink>
        </AboutClosing>
    </div>
</template>
