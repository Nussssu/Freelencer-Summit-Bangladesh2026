<script setup lang="ts">
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import LogoWall from '@/components/inner/LogoWall.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, SectionHeading } from '@/types/inner';

// Brief §10: sponsors by category (Title, Platinum, Gold, Silver, partners); confirmed logos appear automatically.
defineProps<{
    page: InnerPage;
    content: { headings: Record<'tiers' | 'become', SectionHeading>; provisional: boolean; tiers: { name: string; sponsors: { name: string; logo: string | null }[] }[]; note: string };
}>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.tiers"><span v-if="content.provisional" class="in-pill">Provisional</span></SectionHead>
                <LogoWall :tiers="content.tiers" />
                <p class="in-meta mt-5">{{ content.note }}</p>
            </div>
        </section>
    </div>

    <CtaBand title="Your logo here" emphasis="here" text="Sponsor packages from Title to Media Partner, with visibility across the national journey and the Grand Summit.">
        <InBtn href="/sponsorship-opportunity" tone="gold">Become a Sponsor</InBtn>
    </CtaBand>
</template>
