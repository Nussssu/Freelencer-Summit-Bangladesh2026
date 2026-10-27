<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Partners } from '@/types/content';

// Section 13. GITEX named tier bands between hairlines (G8). Data-driven: confirmed sponsors fill slots automatically.
defineProps<{ partners: Partners; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
const cols = (slots: number) => (slots === 1 ? 'grid-cols-1 max-w-sm' : slots === 2 ? 'grid-cols-2 max-w-2xl' : slots === 3 ? 'grid-cols-2 sm:grid-cols-3 max-w-4xl' : 'grid-cols-2 sm:grid-cols-4');
</script>

<template>
    <section ref="root" data-section="13" aria-labelledby="pt-title" class="on-light section relative z-[1] border-t border-line-light">
        <div class="container-site">
            <p class="t-label text-center text-green">{{ t('sections.partners') }}<span v-if="partners.provisional"> · {{ t('common.provisional') }}</span></p>
            <SplitHeading id="pt-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3 text-center" />
            <div class="mt-12 space-y-10">
                <div v-for="tier in partners.tiers" :key="tier.name">
                    <h3 class="t-label flex items-center gap-4 text-ink-2 before:h-px before:flex-1 before:bg-line-light after:h-px after:flex-1 after:bg-line-light">{{ tier.name }}</h3>
                    <ul class="mx-auto mt-4 grid gap-3" :class="cols(tier.slots)">
                        <li v-for="s in tier.sponsors" :key="s.name" class="grid min-h-24 place-items-center border border-line-light p-4">
                            <a :href="s.href" target="_blank" rel="noopener" class="logo"><img :src="s.logo" :alt="s.name" class="max-h-14 w-auto" loading="lazy" /></a>
                        </li>
                        <li v-for="n in Math.max(0, tier.slots - tier.sponsors.length)" :key="`e${n}`" class="slot t-label text-ink-2" data-reveal="fade">{{ t('common.yourLogo') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.logo img {
    filter: grayscale(1);
    opacity: 0.8;
    transition:
        filter 0.3s ease-in-out,
        opacity 0.3s ease-in-out;
}
.logo:hover img,
.logo:focus-visible img {
    filter: none;
    opacity: 1;
}
</style>
