<script setup lang="ts">
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Accordion from '@/components/ui/Accordion.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, SectionHeading } from '@/types/inner';

// Brief §12: the forum as a long-term ecosystem: purpose, benefits, eligibility, how to join, community, member portal, FAQ.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'purpose' | 'benefits' | 'how' | 'activities' | 'faq', SectionHeading>;
        forum: { intro: string; purpose: string; eligibility: string; portalNote: string };
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
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div class="in-media is-round aspect-[4/3] lg:col-span-5" data-reveal="clip"><img src="/media/photos/involved-forum.jpg" alt="" loading="lazy" /></div>
                <div class="lg:col-span-7">
                    <SectionHead :heading="content.headings.purpose" />
                    <p class="in-lead -mt-4" data-reveal="fade">{{ content.forum.intro }}</p>
                    <p class="in-body mt-4" data-reveal="fade">{{ content.forum.purpose }}</p>
                    <div class="in-card tone-blue is-washed mt-6 p-5" data-reveal="fade">
                        <p class="in-meta">Eligibility</p>
                        <p class="mt-1 text-night">{{ content.forum.eligibility }}</p>
                    </div>
                </div>
            </div>
        </section>

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
            <div class="container-site grid gap-8 lg:grid-cols-12">
                <div class="lg:col-span-4"><SectionHead :heading="content.headings.faq" /></div>
                <div class="in-card is-solid in-pad lg:col-span-8" data-reveal="fade"><Accordion :items="content.faq" id-prefix="forum-faq" /></div>
            </div>
        </section>
    </div>

    <CtaBand title="Join the BACCO Freelancer Forum" emphasis="Freelancer Forum" text="Apply in a few minutes. Only the details BACCO officially needs are requested.">
        <InBtn href="/forum/register" tone="gold">Join the forum</InBtn>
    </CtaBand>
</template>
