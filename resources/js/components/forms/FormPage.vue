<script setup lang="ts">
import { computed, ref } from 'vue';
import Glyph from '@/components/inner/Glyph.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';

/**
 * The application forms' page from the client mock-ups (register, nominate, exhibit): the About page's teal and gold
 * ground, a pill eyebrow, a large headline with the emphasis in teal, one line of intro, a row of icon points
 * (optionally an image on the right), then one white card holding the form. `section` renders it as a block inside a
 * longer page (h2, spacing left to the section above) instead of the page opener under the header.
 */
const props = withDefaults(
    defineProps<{ eyebrow?: string | null; title: string; emphasis?: string | null; intro?: string | null; points: { icon: GlyphName; text: string }[]; image?: string | null; center?: boolean; label: string; section?: boolean; headingId?: string }>(),
    { eyebrow: null, emphasis: null, intro: null, image: null, center: false, section: false, headingId: 'form-title' },
);
// The page opener always centres its title and points; a section follows `center`.
const centred = computed(() => props.center || !props.section);
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <div ref="root" class="form-ground" :class="section && 'is-section'">
        <section class="relative overflow-hidden" :class="!section && 'isolate bg-night text-paper'" :data-header="section ? undefined : 'light'" :aria-labelledby="headingId">
            <!-- Page opener: the About hero's stage photo under its teal glow -->
            <template v-if="!section">
                <div class="absolute inset-0 -z-10" aria-hidden="true">
                    <img src="/media/photos/form-hero.png" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover object-[50%_40%]" />
                </div>
                <div class="hero-glow absolute inset-0 -z-10" aria-hidden="true" />
            </template>
            <!-- shortcut: xl offset hard-codes the 2-row header (153px), as in PageHero. -->
            <div class="container-site grid items-center gap-8" :class="[image && 'lg:grid-cols-12', section ? 'pt-6 pb-9' : 'pt-[calc(var(--header-h)+1.75rem)] pb-14 xl:pt-[calc(153px+2.25rem)] md:pb-16']">
                <div :class="[image && 'lg:col-span-8', centred && 'text-center']">
                    <!-- The page opener shows no eyebrow pill or intro line, only the title and the points -->
                    <p v-if="eyebrow && section" class="inline-block rounded-full border border-green/70 bg-white/40 px-6 py-1.5 text-sm font-bold tracking-[0.04em] text-night uppercase md:text-base" data-reveal="fade">{{ eyebrow }}</p>
                    <SplitHeading :id="headingId" :as="section ? 'h2' : 'h1'" :text="title" :emphasis="emphasis ?? undefined" :emphasis-class="section ? 'text-green' : 'text-gold'" class="form-title" :class="[eyebrow && section && 'mt-5', centred && 'mx-auto max-w-6xl']" />
                    <p v-if="intro && section" class="mt-3 max-w-3xl text-lg text-ink-2 md:text-xl" :class="centred && 'mx-auto'" data-reveal="fade">{{ intro }}</p>
                    <ul class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center md:text-lg" :class="[centred ? 'items-center sm:justify-center' : '', section ? 'text-night' : 'text-paper']" data-reveal="fade">
                        <li v-for="p in points" :key="p.text" class="flex items-center gap-3 sm:px-6 md:px-8 sm:[&:not(:first-child)]:border-l" :class="[!centred && 'sm:first:pl-0', section ? 'sm:[&:not(:first-child)]:border-night/15' : 'sm:[&:not(:first-child)]:border-white/25']">
                            <Glyph :name="p.icon" class="size-7 shrink-0 md:size-8" :class="section ? 'text-green' : 'text-gold'" />{{ p.text }}
                        </li>
                    </ul>
                </div>
                <img v-if="image" :src="image" alt="" class="pointer-events-none mx-auto hidden w-full max-w-[19rem] select-none lg:col-span-4 lg:block" />
            </div>
        </section>

        <section class="pb-16 md:pb-24" :class="!section && 'pt-12 md:pt-16'" :aria-label="label">
            <div class="container-site">
                <div class="mx-auto max-w-[68rem] rounded-3xl border border-white/80 bg-white/90 p-6 shadow-[0_30px_70px_-40px_rgb(8_58_64/0.4)] backdrop-blur-sm sm:p-10" data-reveal="fade">
                    <slot />
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* The About page's ground: ice blue with soft teal and summit-gold glows */
.form-ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 100%, rgb(234 185 75 / 0.28), transparent 70%),
        var(--mist);
}
/* Inside a longer page: no base colour and the glows kept off the top edge, so it blends into the section above */
.form-ground.is-section {
    background:
        radial-gradient(900px 640px at 0% 45%, rgb(8 127 140 / 0.16), transparent 70%),
        radial-gradient(900px 640px at 100% 100%, rgb(234 185 75 / 0.28), transparent 70%);
}
/* The About hero's teal glow, deepening to night at the edges */
.hero-glow {
    background: radial-gradient(70% 95% at 40% 50%, rgb(8 127 140 / 0.8), rgb(6 69 76 / 0.86) 45%, rgb(7 20 17 / 0.95) 100%);
}
.form-title {
    font-family: var(--font-sans);
    font-weight: 800;
    letter-spacing: -0.02em;
    line-height: 1.05;
    font-size: clamp(2.5rem, 5.5vw, 4.5rem);
}
/* Field labels in the mock-ups' weight */
.form-ground :deep(:is(label, legend).block) {
    font-weight: 600;
}
</style>
