<script setup lang="ts">
import { ref } from 'vue';
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
withDefaults(
    defineProps<{ eyebrow: string; title: string; emphasis?: string | null; intro?: string | null; points: { icon: GlyphName; text: string }[]; image?: string | null; center?: boolean; label: string; section?: boolean; headingId?: string }>(),
    { emphasis: null, intro: null, image: null, center: false, section: false, headingId: 'form-title' },
);
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <div ref="root" class="form-ground" :class="section && 'is-section'">
        <section class="relative overflow-hidden" :data-header="section ? undefined : 'light'" :aria-labelledby="headingId">
            <!-- shortcut: xl offset hard-codes the 2-row header (153px), as in PageHero. -->
            <div class="container-site grid items-center gap-8 pb-9" :class="[image && 'lg:grid-cols-12', section ? 'pt-6' : 'pt-[calc(var(--header-h)+1.75rem)] xl:pt-[calc(153px+2.25rem)]']">
                <div :class="[image && 'lg:col-span-8', center && 'text-center']">
                    <p class="inline-block rounded-full border border-green/70 bg-white/40 px-6 py-1.5 text-sm font-bold tracking-[0.04em] text-night uppercase md:text-base" data-reveal="fade">{{ eyebrow }}</p>
                    <SplitHeading :id="headingId" :as="section ? 'h2' : 'h1'" :text="title" :emphasis="emphasis ?? undefined" emphasis-class="text-green" class="form-title mt-5" :class="center && 'mx-auto max-w-6xl'" />
                    <p v-if="intro" class="mt-3 max-w-3xl text-lg text-ink-2 md:text-xl" :class="center && 'mx-auto'" data-reveal="fade">{{ intro }}</p>
                    <ul class="mt-7 flex flex-col gap-3 text-night sm:flex-row sm:flex-wrap sm:items-center md:text-lg" :class="center ? 'items-center sm:justify-center' : ''" data-reveal="fade">
                        <li v-for="p in points" :key="p.text" class="flex items-center gap-3 sm:px-6 md:px-8 sm:[&:not(:first-child)]:border-l sm:[&:not(:first-child)]:border-night/15" :class="!center && 'sm:first:pl-0'">
                            <Glyph :name="p.icon" class="size-7 shrink-0 text-green md:size-8" />{{ p.text }}
                        </li>
                    </ul>
                </div>
                <img v-if="image" :src="image" alt="" class="pointer-events-none mx-auto hidden w-full max-w-[19rem] select-none lg:col-span-4 lg:block" />
            </div>
        </section>

        <section class="pb-16 md:pb-24" :aria-label="label">
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
