<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import DetailLayout from '@/components/inner/DetailLayout.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { ExhibitorCard, InnerPage } from '@/types/inner';

// Brief §11 exhibitor profile: logo, description, website, sector and booth.
defineProps<{ page: InnerPage; content: { exhibitor: ExhibitorCard & { description: string | null; website: string | null }; facts: { icon: GlyphName; label: string; value: string }[] } }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page">
        <template v-if="content.exhibitor.sample" #actions><SampleRibbon /></template>
    </InnerHero>

    <DetailLayout aside-label="Exhibitor details">
        <div class="flex items-center gap-5">
            <span class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-night/8 bg-snow p-3">
                <img v-if="content.exhibitor.logo" :src="content.exhibitor.logo" :alt="content.exhibitor.name" class="max-h-full w-auto" />
                <span v-else class="text-3xl font-extrabold text-green">{{ content.exhibitor.name.slice(0, 2) }}</span>
            </span>
            <h2 class="in-h3 text-2xl!">About {{ content.exhibitor.name }}</h2>
        </div>
        <p class="in-body mt-6">{{ content.exhibitor.description ?? 'A full profile is published when the exhibitor confirms its details.' }}</p>

        <template #aside>
            <dl class="space-y-4">
                <div v-for="f in content.facts" :key="f.label" class="tone-violet flex items-center gap-3">
                    <span class="in-disc w-11!"><Glyph :name="f.icon" /></span>
                    <div><dt class="in-meta">{{ f.label }}</dt><dd class="font-bold text-night">{{ f.value }}</dd></div>
                </div>
            </dl>
            <div class="flex flex-wrap gap-3 border-t border-night/8 pt-5">
                <InBtn v-if="content.exhibitor.website" :href="content.exhibitor.website" external>Visit website</InBtn>
                <ShareButton :title="content.exhibitor.name" />
            </div>
            <Link href="/exhibition#directory" class="in-link">← All exhibitors</Link>
        </template>
    </DetailLayout>
</template>
