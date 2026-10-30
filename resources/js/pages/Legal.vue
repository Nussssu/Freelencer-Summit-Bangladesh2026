<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InnerHero from '@/components/inner/InnerHero.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { InnerPage } from '@/types/inner';

// Privacy policy and terms: the organiser's approved text from the pages table, or a clearly marked draft summary.
defineProps<{ page: InnerPage; content: { sections: { h: string; p: string[] }[]; draft: boolean; draftNote: string; updatedAt: string | null } }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <section class="in-section pt-0!">
        <div class="container-site grid gap-8 lg:grid-cols-12">
            <nav class="lg:col-span-4" aria-label="On this page">
                <div class="in-card is-washed tone-teal in-pad lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
                    <p v-if="content.draft" class="in-pill bg-gold/30! text-night!">Draft</p>
                    <p v-if="content.draft" class="mt-3 text-sm text-ink-2">{{ content.draftNote }}</p>
                    <p v-if="content.updatedAt" class="in-meta mt-3">Last updated {{ content.updatedAt }}</p>
                    <ol class="mt-5 space-y-1 border-t border-night/8 pt-4">
                        <li v-for="(s, i) in content.sections" :key="s.h"><a :href="`#s${i}`" class="in-link min-h-10! text-[0.9375rem]">{{ s.h }}</a></li>
                    </ol>
                    <p class="mt-4 border-t border-night/8 pt-4 text-sm"><Link href="/contact" class="font-semibold text-green">Questions? Contact the team</Link></p>
                </div>
            </nav>
            <article class="in-card is-solid in-pad lg:col-span-8">
                <div class="in-prose max-w-none">
                    <template v-for="(s, i) in content.sections" :key="s.h">
                        <h2 :id="`s${i}`" class="scroll-mt-[calc(var(--header-h)+1rem)] first:mt-0">{{ s.h }}</h2>
                        <p v-for="p in s.p" :key="p">{{ p }}</p>
                    </template>
                </div>
            </article>
        </div>
    </section>
</template>
