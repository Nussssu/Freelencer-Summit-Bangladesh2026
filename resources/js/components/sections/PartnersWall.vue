<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Partners } from '@/types/content';

// Section 13. GITEX named tier bands between hairlines (G8). Data-driven: tiers without sponsors are hidden.
defineProps<{ partners: Partners; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" data-section="13" aria-labelledby="pt-title" class="on-light section relative z-[1] border-t border-line-light">
        <div class="container-site">
            <p class="t-label text-center text-green">{{ t('sections.partners') }}<span v-if="partners.provisional"> · {{ t('common.provisional') }}</span></p>
            <SplitHeading id="pt-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3 text-center" />
            <div class="mt-14 space-y-14">
                <div v-for="tier in partners.tiers.filter((x) => x.sponsors.length)" :key="tier.name" data-reveal="fade">
                    <h3 class="t-label mx-auto flex max-w-3xl items-center gap-6 text-center text-ink-2 before:h-px before:flex-1 before:bg-line-light after:h-px after:flex-1 after:bg-line-light">{{ tier.name }}</h3>
                    <ul class="mx-auto mt-6 flex max-w-5xl flex-wrap items-center justify-center gap-x-14 gap-y-6">
                        <li v-for="s in tier.sponsors" :key="s.name" class="grid min-h-14 place-items-center">
                            <a v-if="s.logo" :href="s.href" target="_blank" rel="noopener" class="logo"><img :src="s.logo" :alt="s.name" class="max-h-14 w-auto" loading="lazy" /></a>
                            <span v-else class="text-center font-display text-2xl font-black tracking-wide text-ink-2 uppercase">{{ s.name }}</span>
                        </li>
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
