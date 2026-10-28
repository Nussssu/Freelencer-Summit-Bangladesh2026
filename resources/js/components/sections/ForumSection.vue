<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Accordion from '@/components/ui/Accordion.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Forum, Heading } from '@/types/content';

// Section 11. A long-term ecosystem initiative, not a form (brief §12). BACCO blue is the family colour here (B2).
defineProps<{ forum: Forum; heading: Heading }>();
const { t } = useI18n();
const { site } = useShared();
const root = ref<HTMLElement>();
useReveal(root);
const cta = computed(() => site.value.ctas.primary.find((c) => c.id === 'forum')!);
</script>

<template>
    <section ref="root" data-section="11" aria-labelledby="forum-title" class="on-light section relative z-[1] border-t-8 border-bacco bg-snow">
        <div class="container-site grid grid-cols-1 gap-12 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-5">
                <p class="t-label text-bacco">{{ t('sections.forum') }}</p>
                <SplitHeading id="forum-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-bacco" class="t-h2 mt-3" />
                <p class="mt-6 text-lg" data-reveal="fade">{{ forum.intro }}</p>
                <h3 class="t-label mt-8 text-ink-2">{{ t('sections.forumPurpose') }}</h3>
                <p class="mt-2" data-reveal="fade">{{ forum.purpose }}</p>
                <h3 class="t-label mt-6 text-ink-2">{{ t('sections.eligibility') }}</h3>
                <p class="mt-2" data-reveal="fade">{{ forum.eligibility }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <CtaLink :href="cta.href" tone="blue">{{ cta.label }}</CtaLink>
                    <span class="t-label border border-dashed border-ink-2 px-2 py-1 text-ink-2">{{ forum.portalNote }}</span>
                </div>
            </div>
            <div class="min-w-0 lg:col-span-6 lg:col-start-7">
                <h3 class="t-label text-ink-2">{{ t('sections.forumBenefits') }}</h3>
                <ol class="mt-3 border-t border-night">
                    <li v-for="(b, i) in forum.benefits" :key="b.title" class="grid grid-cols-[4rem_1fr] gap-4 border-b border-line-light py-5" data-reveal="fade">
                        <span class="font-display text-5xl leading-none font-black text-bacco">{{ String(i + 1).padStart(2, '0') }}</span>
                        <div>
                            <p class="font-display text-2xl font-black uppercase">{{ b.title }}</p>
                            <p class="mt-1 text-ink-2">{{ b.text }}</p>
                        </div>
                    </li>
                </ol>
                <h3 class="t-label mt-10 text-ink-2">{{ t('sections.faq') }}</h3>
                <Accordion :items="forum.faq" id-prefix="forum-faq" heading-level="h4" class="mt-3" />
            </div>
        </div>
    </section>
</template>
