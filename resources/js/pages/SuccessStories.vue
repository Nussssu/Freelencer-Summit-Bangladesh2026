<script setup lang="ts">
import { computed, ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, Option, SectionHeading } from '@/types/inner';

type Story = { name: string; role: string | null; kind: string; title: string; quote: string | null; image: string | null; youtubeId: string | null; sample: boolean };
// Brief §6, §16: profiles and videos of Bangladeshi freelancers and entrepreneurs (sample people until real stories are published).
const props = defineProps<{
    page: InnerPage;
    content: { headings: Record<'stories' | 'videos' | 'share', SectionHeading>; stories: Story[]; filters: Option[]; videos: { title: string; poster: string; youtubeId: string | null }[]; videoSoon: string; share: string };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const kind = ref('');
const shown = computed(() => props.content.stories.filter((s) => !kind.value || s.kind === kind.value));
const tones = ['tone-sky', 'tone-sun', 'tone-mint'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.stories"><FilterChips v-model="kind" :options="content.filters" label="Show" /></SectionHead>
                <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(s, i) in shown" :key="s.name" data-reveal="fade">
                        <article class="in-card is-washed h-full overflow-hidden" :class="tones[i % 3]">
                            <span class="in-media aspect-[4/3]">
                                <img v-if="s.image" :src="s.image" :alt="s.name" loading="lazy" class="object-top" />
                                <span class="absolute top-3 left-3 flex gap-2"><span class="in-pill bg-white/90!">{{ s.kind }}</span><SampleRibbon v-if="s.sample" /></span>
                            </span>
                            <div class="in-pad flex flex-1 flex-col">
                                <h3 class="in-h3">{{ s.title }}</h3>
                                <blockquote v-if="s.quote" class="mt-3 flex-1 text-lg leading-snug text-night">“{{ s.quote }}”</blockquote>
                                <p class="mt-5 font-bold text-night">{{ s.name }}</p>
                                <p v-if="s.role" class="in-meta">{{ s.role }}</p>
                            </div>
                        </article>
                    </li>
                </ul>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.videos" />
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="v in content.videos" :key="v.title" class="in-card is-solid overflow-hidden" data-reveal="fade">
                        <span class="in-media aspect-video">
                            <img :src="v.poster" alt="" loading="lazy" />
                            <span class="absolute inset-0 grid place-items-center bg-night/25">
                                <span class="grid size-16 place-items-center rounded-full bg-white/90 text-green shadow-lg"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z" /></svg></span>
                            </span>
                        </span>
                        <div class="flex items-center justify-between gap-3 p-5">
                            <h3 class="in-h3 text-lg!">{{ v.title }}</h3>
                            <span class="in-pill shrink-0">{{ content.videoSoon }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.share.title" :emphasis="content.headings.share.emphasis ?? undefined" :text="content.share">
        <InBtn href="/contact?topic=media" tone="gold">Share your story</InBtn>
        <InBtn href="/forum" tone="ghost">BACCO Freelancer Forum</InBtn>
    </CtaBand>
</template>
