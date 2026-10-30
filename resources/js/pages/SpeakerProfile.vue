<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DetailLayout from '@/components/inner/DetailLayout.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import PersonCard from '@/components/inner/PersonCard.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, SpeakerCard } from '@/types/inner';

// Brief §13 profile: photo, name, designation, organisation, biography, expertise, links; sessions once the agenda is final.
defineProps<{
    page: InnerPage;
    content: {
        speaker: { slug: string; name: string; role: string; designation: string | null; organisation: string | null; country: string | null; photo: string | null; bio: string | null; expertise: string[]; links: { label: string; href: string }[] };
        facts: { icon: GlyphName; label: string; value: string }[];
        bioEmpty: string;
        sessionsEmpty: string;
        others: SpeakerCard[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" :image="content.speaker.photo ?? undefined" />
    <InnerHero :page="page">
        <template #actions><span class="in-pill bg-gold! text-night!">{{ content.speaker.role }}</span></template>
        <template #aside>
            <div class="mx-auto max-w-sm" data-reveal="clip">
                <div class="in-media is-round aspect-[4/5] shadow-[0_30px_60px_-34px_rgb(7_20_17/0.45)]">
                    <img v-if="content.speaker.photo" :src="content.speaker.photo" :alt="content.speaker.name" class="object-top" />
                </div>
            </div>
        </template>
    </InnerHero>

    <div ref="root">
        <DetailLayout aside-label="Speaker details">
            <h2 class="in-h3 text-2xl!">Biography</h2>
            <p class="in-body mt-4">{{ content.speaker.bio ?? content.bioEmpty }}</p>
            <h2 class="in-h3 mt-10 text-2xl!">Expertise</h2>
            <ul v-if="content.speaker.expertise.length" class="mt-4 flex flex-wrap gap-2">
                <li v-for="e in content.speaker.expertise" :key="e" class="in-pill">{{ e }}</li>
            </ul>
            <p v-else class="in-body mt-4">Published with the speaker announcement.</p>
            <h2 class="in-h3 mt-10 text-2xl!">Sessions</h2>
            <p class="in-body mt-4">{{ content.sessionsEmpty }}</p>
            <div class="mt-5"><InBtn href="/agenda" tone="ghost">See the agenda</InBtn></div>

            <template #aside>
                <dl class="space-y-4">
                    <div v-for="f in content.facts" :key="f.label" class="tone-teal flex items-center gap-3">
                        <span class="in-disc w-11!"><Glyph :name="f.icon" /></span>
                        <div class="min-w-0">
                            <dt class="in-meta">{{ f.label }}</dt>
                            <dd class="font-bold text-night">{{ f.value }}</dd>
                        </div>
                    </div>
                </dl>
                <ul v-if="content.speaker.links.length" class="space-y-1 border-t border-night/8 pt-5">
                    <li v-for="l in content.speaker.links" :key="l.href"><a :href="l.href" target="_blank" rel="noopener" class="in-link">{{ l.label }}</a></li>
                </ul>
                <div class="flex flex-wrap gap-3 border-t border-night/8 pt-5">
                    <InBtn href="/register" tone="gold">Register to attend</InBtn>
                    <ShareButton :title="content.speaker.name" />
                </div>
                <Link href="/speakers" class="in-link">← All speakers</Link>
            </template>

            <template v-if="content.others.length" #related>
                <SectionHead :heading="{ title: 'More speakers', emphasis: 'speakers' }" />
                <ul class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <li v-for="p in content.others" :key="p.slug"><PersonCard :person="p" /></li>
                </ul>
            </template>
        </DetailLayout>
    </div>
</template>
