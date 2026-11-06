<script setup lang="ts">
import { ref } from 'vue';
import AboutHero from '@/components/about/AboutHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import PartnersWall from '@/components/sections/PartnersWall.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Partners } from '@/types/content';
import type { InnerPage, SectionHeading } from '@/types/inner';

// Partners: About-style photo hero, the two organisers (BACCO, DoICT), then the landing's partner wall as is, under
// its own title.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'organisers', SectionHeading> & { wall: Heading };
        organisers: { short: string; name: string; role: string; logo: string | null }[];
        partners: Partners;
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root">
        <section class="in-section">
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
    </div>

    <PartnersWall :partners="content.partners" :heading="content.headings.wall" />
</template>
