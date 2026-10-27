<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Marquee from '@/components/ui/Marquee.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';

// Section 16. Full-bleed flag green, giant line, stitch underline, all five journeys; faint "Register now" marquee behind.
defineProps<{ finalCta: { line: string; marquee: string } }>();
const { site } = useShared();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" data-section="16" aria-labelledby="final-title" class="grain relative z-[1] overflow-hidden bg-green py-24 md:py-32">
        <div class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 font-display text-[22vw] leading-none font-black text-paper/[0.06] uppercase" aria-hidden="true">
            <Marquee :items="[finalCta.marquee, finalCta.marquee]" :duration="50" separator="·" />
        </div>
        <div class="relative px-4 md:px-8">
            <h2 id="final-title" class="font-display text-[15vw] leading-[0.82] font-black text-paper uppercase" data-reveal="fade">{{ finalCta.line }}</h2>
            <div class="stitch mt-6 h-2 max-w-[60vw]" data-reveal="stitch" />
            <ul class="mt-12 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <li v-for="c in site.ctas.primary" :key="c.id">
                    <CtaLink :href="c.href" :tone="c.tone === 'green' ? 'outline' : c.tone" class="w-full sm:w-auto" :class="c.tone === 'outline' && 'text-paper'">{{ c.id === 'register' ? t('cta.register') : c.label }}</CtaLink>
                </li>
            </ul>
        </div>
    </section>
</template>
