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

type Logo = { name: string; logo: string | null };
// Partners: organisers, government/development, strategic and supporting partners (as the landing's trust strip), then sponsors.
const props = defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'organisers' | 'groups' | 'sponsors' | 'become', SectionHeading>;
        organisers: { short: string; name: string; role: string; logo: string | null }[];
        groups: { label: string; partners: Logo[] }[];
        tiers: { name: string; sponsors: Logo[] }[];
        provisional: boolean;
        note: string;
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const groups = props.content.groups.map((g) => ({ name: g.label, sponsors: g.partners }));
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.organisers" />
                <ul class="grid gap-5 md:grid-cols-2">
                    <li v-for="o in content.organisers" :key="o.short" class="in-card is-solid flex-row! items-center gap-5 p-5 md:p-6" data-reveal="fade">
                        <span class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-night/8 bg-snow p-2">
                            <img v-if="o.logo" :src="o.logo" :alt="o.short" class="max-h-full w-auto" />
                            <span v-else class="text-2xl font-extrabold text-bacco">{{ o.short }}</span>
                        </span>
                        <span>
                            <span class="in-meta">{{ o.role }}</span>
                            <span class="in-h3 mt-1 block">{{ o.name }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.groups"><span v-if="content.provisional" class="in-pill">Provisional</span></SectionHead>
                <LogoWall :tiers="groups" />
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.sponsors"><InBtn href="/sponsors" tone="ghost">Sponsors page</InBtn></SectionHead>
                <LogoWall :tiers="content.tiers" />
                <p class="in-meta mt-5">{{ content.note }}</p>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.become.title" :emphasis="content.headings.become.emphasis ?? undefined" text="Government, development, strategic and supporting partners help carry the campaign to every division.">
        <InBtn href="/sponsorship-opportunity" tone="gold">Become a Sponsor</InBtn>
        <InBtn href="/contact?topic=sponsorship" tone="ghost">Talk to the team</InBtn>
    </CtaBand>
</template>
