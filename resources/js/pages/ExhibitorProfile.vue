<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InBtn from '@/components/inner/InBtn.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { ExhibitorCard, InnerPage } from '@/types/inner';

// Brief §11 exhibitor profile in the ai-expo.net partner-page style: back link to the list, then a white profile sheet
// with the logo panel and stand number on the left, and the name, sector tag, full description, website and share
// on the right.
defineProps<{ page: InnerPage; content: { exhibitor: ExhibitorCard & { description: string | null; website: string | null } } }>();
const root = ref<HTMLElement>();
useReveal(root);
const initials = (name: string) => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
const host = (url: string) => url.replace(/^https?:\/\/(www\.)?/, '').replace(/\/$/, '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <section ref="root" class="in-hero relative isolate overflow-hidden" data-header="light" aria-labelledby="xp-name">
        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero. -->
        <div class="container-site pt-[calc(var(--header-h)+2rem)] pb-[var(--section-gap)] xl:pt-[calc(153px+2rem)]">
            <Link href="/exhibition/exhibitors" class="in-link">← All exhibitors</Link>

            <article class="mt-6 grid overflow-hidden rounded-2xl bg-white shadow-[0_30px_60px_-40px_rgb(7_20_17/0.45)] ring-1 ring-night/8 lg:grid-cols-[22rem_1fr]" data-reveal="fade">
                <!-- Logo and stand -->
                <aside class="flex flex-col border-b border-night/8 lg:border-r lg:border-b-0">
                    <div class="grid aspect-[4/3] place-items-center p-10 lg:aspect-square">
                        <img v-if="content.exhibitor.logo" :src="content.exhibitor.logo" :alt="content.exhibitor.name" class="max-h-full w-auto object-contain" />
                        <span v-else class="font-display text-7xl font-black text-green" aria-hidden="true">{{ initials(content.exhibitor.name) }}</span>
                    </div>
                    <div class="flex items-end justify-between gap-4 border-t border-night/8 bg-mist/60 px-8 py-6">
                        <div>
                            <p class="text-sm font-bold">Stand</p>
                            <p class="font-display text-5xl leading-none font-black">{{ content.exhibitor.booth ?? 'TBA' }}</p>
                        </div>
                        <Link href="/exhibition" class="in-link text-sm">Floor plan →</Link>
                    </div>
                </aside>

                <!-- Name, sector, description, links -->
                <div class="p-8 md:p-12">
                    <h1 id="xp-name" class="text-[clamp(2.25rem,4vw,3.5rem)] leading-[1.05] font-extrabold tracking-[-0.02em]">{{ content.exhibitor.name }}</h1>
                    <ul class="mt-5 flex flex-wrap gap-3">
                        <li class="rounded bg-green px-4 py-2 text-sm font-bold text-white">{{ content.exhibitor.sector }}</li>
                    </ul>
                    <div class="mt-8 border-t border-night/8 pt-8">
                        <h2 class="text-sm font-bold tracking-wide text-ink-2 uppercase">About</h2>
                        <p class="mt-3 text-[1.0625rem] leading-[1.9] whitespace-pre-line text-night/85">{{ content.exhibitor.description ?? 'A full profile is published when the exhibitor confirms its details.' }}</p>
                    </div>
                    <div class="mt-10 flex flex-wrap items-center gap-3">
                        <InBtn v-if="content.exhibitor.website" :href="content.exhibitor.website" external tone="gold">{{ host(content.exhibitor.website) }}</InBtn>
                        <ShareButton :title="content.exhibitor.name" />
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
