<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Breadcrumbs from '@/components/inner/Breadcrumbs.vue';
import Photo from '@/components/inner/Photo.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { EASE, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { InnerPage } from '@/types/inner';

// Photo-led About hero: copy left, framed keynote photo right (slow clip wipe + settle, soft scroll parallax),
// date / venue / city sit under the photo as part of the composition. data-header keeps the light header from the top.
defineProps<{ page: InnerPage; image: string }>();
const { t } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const facts = computed(() => [
    { label: t('inner.tbc.date'), value: summit.value.dateConfirmed ? new Date(summit.value.startsAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.lateNov', { edition: summit.value.edition }), tbc: !summit.value.dateConfirmed },
    { label: t('inner.tbc.venue'), value: summit.value.venue ?? t('inner.tbc.toBeConfirmed'), tbc: !summit.value.venue },
    { label: t('inner.tbc.city'), value: summit.value.city, tbc: false },
]);

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-hero-frame]', { clipPath: 'inset(100% 0 0 0)' }, { clipPath: 'inset(0% 0 0 0)', duration: 1.8, ease: EASE, delay: 0.15 });
    gsap.fromTo('[data-hero-frame] img', { scale: 1.18 }, { scale: 1.06, duration: 2.4, ease: EASE, delay: 0.15 });
    gsap.fromTo('[data-hero-panel]', { opacity: 0, x: 24 }, { opacity: 1, x: 0, duration: 1.2, ease: EASE, delay: 0.5 });
    gsap.to('[data-hero-frame] img', { yPercent: 5, ease: 'none', scrollTrigger: { trigger: root.value, start: 'top top', end: 'bottom top', scrub: true } });
});
</script>

<template>
    <section ref="root" class="on-light light-tokens relative isolate overflow-hidden bg-paper" data-header="light" aria-labelledby="about-title">
        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero; drop it once --header-h is fixed in app.css. -->
        <div class="container-site pt-[calc(var(--header-h)+2rem)] pb-16 md:pb-20 xl:pt-[calc(153px+2rem)]">
            <Breadcrumbs :items="page.crumbs" class="opacity-80" />
            <div class="mt-8 grid gap-12 lg:grid-cols-12 lg:items-center lg:gap-x-10">
                <div class="lg:col-span-6">
                    <p class="flex items-center gap-3 text-sm font-semibold text-green" data-reveal="fade">
                        <span class="stitch w-10 shrink-0" aria-hidden="true" />{{ page.hero.eyebrow }}
                    </p>
                    <SplitHeading
                        id="about-title"
                        as="h1"
                        :text="page.hero.title"
                        :emphasis="page.hero.emphasis"
                        emphasis-class="text-green"
                        class="mt-5 font-display text-[clamp(3rem,6.4vw,6rem)] leading-[0.9] font-black tracking-[-0.01em] uppercase [text-wrap:balance]"
                    />
                    <p class="mt-7 max-w-[52ch] text-lg leading-relaxed text-ink-2" data-reveal="fade">{{ page.hero.intro }}</p>
                    <div class="mt-9 flex flex-wrap gap-3" data-reveal="fade">
                        <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
                    </div>
                </div>

                <div class="relative lg:col-span-6">
                    <!-- Layer 1: soft mist plate offset behind the photo; layer 2: the photo; layer 3: facts panel overlapping the frame -->
                    <div class="absolute -top-5 -right-4 bottom-16 left-10 bg-mist md:-right-6" aria-hidden="true" />
                    <div data-hero-frame class="relative">
                        <Photo :src="image" eager position="center 35%" class="aspect-[4/3] w-full md:aspect-[5/4]" />
                    </div>
                    <div data-hero-panel class="relative -mt-10 ml-auto w-[calc(100%-1.5rem)] bg-snow shadow-[0_18px_40px_-24px_rgb(7_20_17/0.35)] sm:w-[88%]">
                        <div class="stitch absolute -top-1 left-6 w-24" aria-hidden="true" />
                        <dl class="grid grid-cols-3">
                            <div v-for="f in facts" :key="f.label" class="border-r border-line-light px-4 py-5 last:border-r-0 sm:px-6">
                                <dt class="t-label text-ink-2">{{ f.label }}</dt>
                                <dd class="mt-1.5 text-base leading-snug font-semibold sm:text-lg" :class="f.tbc && 'text-ink-2'">{{ f.value }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
