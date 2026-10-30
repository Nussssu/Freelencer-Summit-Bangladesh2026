<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Dialog from '@/components/ui/Dialog.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Heading, Speaker } from '@/types/content';

// Section 7. BPO role labels + "Details" (B6), reinterpreted as a right-side drawer. Every slot starts as
// "Revealing soon" (blurred silhouette); the data shape is ready for real speakers.
const props = defineProps<{ speakers: Speaker[]; heading: Heading }>();
const { t } = useI18n();
const { site } = useShared();
const cta = computed(() => site.value.ctas.secondary.find((c) => c.id === 'speakers')!);
const root = ref<HTMLElement>();
useReveal(root);
const featured = computed(() => props.speakers.find((s) => s.featured) ?? props.speakers[0]);
const rest = computed(() => props.speakers.filter((s) => s !== featured.value));
const selected = ref<Speaker | null>(null);
const drawerOpen = computed({ get: () => !!selected.value, set: (v) => !v && (selected.value = null) });
const nameOf = (s: Speaker) => s.name ?? t('common.revealingSoon');
</script>

<template>
    <section ref="root" data-section="7" aria-labelledby="sp-title" class="on-light flow section relative z-[1]">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <SplitHeading id="sp-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2" />
                </div>
                <CtaLink :href="cta.href" tone="ghost">{{ cta.label }}</CtaLink>
            </div>

            <!-- Featured showcase -->
            <article class="mt-12 card grid grid-cols-1 overflow-hidden md:grid-cols-12" data-reveal="fade">
                <div class="silhouette relative aspect-[4/3] md:col-span-5 md:aspect-auto md:min-h-80" aria-hidden="true">
                    <img v-if="featured.photo" :src="featured.photo" alt="" class="h-full w-full object-cover" />
                </div>
                <div class="flex flex-col justify-center gap-4 p-6 md:col-span-7 md:p-10">
                    <p class="t-label text-gold-ink">{{ t('sections.featured') }} · {{ featured.role }}</p>
                    <h3 class="font-display text-5xl font-black uppercase md:text-6xl">{{ nameOf(featured) }}</h3>
                    <p class="text-lg text-ink-2">{{ featured.credential ?? ([featured.designation, featured.organisation].filter(Boolean).join(', ') || t('sections.announcement')) }}</p>
                    <button type="button" class="btn btn-ghost self-start" @click="selected = featured">{{ t('common.details') }}</button>
                </div>
            </article>

            <ul class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <li v-for="s in rest" :key="s.slug" class="card card-hover flex flex-col overflow-hidden" data-reveal="fade">
                    <div class="silhouette aspect-[4/5]" aria-hidden="true"><img v-if="s.photo" :src="s.photo" alt="" class="h-full w-full object-cover" /></div>
                    <div class="flex min-w-0 flex-1 flex-col gap-1 p-3 sm:p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="t-label bg-gold px-2 py-1 text-night">{{ s.role }}</span>
                            <span class="t-label text-ink-2">{{ s.country ?? t('common.tba') }}</span>
                        </div>
                        <h3 class="mt-3 font-display text-xl font-black uppercase sm:text-2xl">{{ nameOf(s) }}</h3>
                        <p class="text-sm text-ink-2">{{ s.designation ?? t('sections.announcement') }}</p>
                        <p v-if="s.organisation" class="text-sm font-bold">{{ s.organisation }}</p>
                        <button type="button" class="link-u t-label mt-auto inline-flex min-h-11 items-center self-start pt-3 text-green" :aria-label="`${t('common.details')}: ${s.role}, ${nameOf(s)}`" @click="selected = s">{{ t('common.details') }} →</button>
                    </div>
                </li>
            </ul>
        </div>

        <Dialog v-model:open="drawerOpen" :label="selected ? `${selected.role}: ${nameOf(selected)}` : ''" tone="light">
            <template v-if="selected">
                <div class="silhouette aspect-square w-40" aria-hidden="true"><img v-if="selected.photo" :src="selected.photo" alt="" class="h-full w-full object-cover" /></div>
                <p class="t-label mt-6 text-gold-ink">{{ selected.role }} · {{ selected.country ?? t('common.tba') }}</p>
                <h2 class="mt-2 font-display text-4xl font-black uppercase">{{ nameOf(selected) }}</h2>
                <p class="mt-1 text-ink-2">{{ [selected.designation, selected.organisation].filter(Boolean).join(', ') || t('sections.announcement') }}</p>
                <p class="mt-6 leading-relaxed">{{ selected.bio ?? t('sections.bioSoon') }}</p>
                <h3 class="t-label mt-8 text-green">{{ t('sections.expertise') }}</h3>
                <p class="mt-2 text-ink-2">{{ selected.expertise.length ? selected.expertise.join(' · ') : t('common.tba') }}</p>
                <h3 class="t-label mt-6 text-green">{{ t('sections.links') }}</h3>
                <ul class="mt-2"><li v-for="l in selected.links" :key="l.href"><a :href="l.href" class="link-u" target="_blank" rel="noopener">{{ l.label }}</a></li><li v-if="!selected.links.length" class="text-ink-2">{{ t('common.tba') }}</li></ul>
                <Link :href="`/speaker/${selected.slug}`" class="btn mt-8">{{ t('sections.profile') }}</Link>
            </template>
        </Dialog>
    </section>
</template>

<style scoped>
.silhouette {
    background:
        radial-gradient(circle at 50% 38%, rgb(8 112 122 / 0.18) 0 14%, transparent 15%),
        radial-gradient(ellipse 34% 30% at 50% 95%, rgb(8 112 122 / 0.14) 0 98%, transparent 100%),
        linear-gradient(160deg, var(--mist), var(--paper-2));
}
.silhouette:not(:has(img)) {
    filter: blur(1.5px);
}
</style>
