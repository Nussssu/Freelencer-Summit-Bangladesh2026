<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';
import type { Exhibition, Heading, Sponsorship } from '@/types/content';
import EnquiryForm from './expo/EnquiryForm.vue';

// Section 10. Split screen with a diagonal divider: exhibition (paper) | sponsorship (deep green).
defineProps<{ exhibition: Exhibition; sponsorship: Sponsorship; headings: { exhibition: Heading; sponsorship: Heading } }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const enquiring = ref(false);
const { site } = useShared();
const cta = (id: string) => site.value.ctas.primary.find((c) => c.id === id)!;
useReveal(root);
const states = ['available', 'reserved', 'confirmed'] as const;
const stateClass = { available: 'bg-mint/70', reserved: 'bg-gold', confirmed: 'bg-red' };
</script>

<template>
    <section ref="root" data-section="10" :aria-label="`${t('sections.exhibition')} · ${t('sections.sponsorship')}`" class="relative z-[1] grid grid-cols-1 lg:grid-cols-2">
        <!-- Exhibition -->
        <div class="on-light flow section expo-side">
            <div class="mx-auto max-w-[660px] px-4 md:px-8">
                <p class="t-label text-green">{{ t('sections.exhibition') }}</p>
                <SplitHeading :text="headings.exhibition.title" :emphasis="headings.exhibition.emphasis" emphasis-class="text-green" class="t-h2 mt-3" />

                <figure class="card mt-8 p-4" data-reveal="clip">
                    <figcaption class="t-label flex flex-wrap items-center justify-between gap-2 text-ink-2">
                        <span>{{ t('sections.floorPlan') }}<span v-if="exhibition.boothsIllustrative"> · {{ t('common.illustrative') }}</span></span>
                        <span class="flex gap-3">
                            <span v-for="s in states" :key="s" class="flex items-center gap-1"><span class="size-3" :class="stateClass[s]" aria-hidden="true" />{{ t(`sections.booth.${s}`) }}</span>
                        </span>
                    </figcaption>
                    <ul class="mt-4 grid grid-cols-8 gap-1" :aria-label="t('sections.floorPlan')">
                        <li v-for="b in exhibition.booths" :key="b.id" class="t-label grid aspect-square text-[0.625rem] text-night" :title="`${b.id}: ${t(`sections.booth.${b.state}`)}`">
                            <button
                                v-if="b.state === 'available'"
                                type="button"
                                class="grid cursor-pointer place-items-center transition-all duration-300 hover:scale-105 hover:brightness-105 focus-visible:scale-105"
                                :class="stateClass[b.state]"
                                aria-haspopup="dialog"
                                @click="enquiring = true"
                            >
                                <span class="sr-only">{{ t('sections.enquire') }} · </span>{{ b.id }}<span class="sr-only">: {{ t(`sections.booth.${b.state}`) }}</span>
                            </button>
                            <span v-else class="grid cursor-not-allowed place-items-center" :class="stateClass[b.state]">
                                <span class="sr-only">{{ b.id }}: {{ t(`sections.booth.${b.state}`) }}</span>
                            </span>
                        </li>
                    </ul>
                    <p class="t-label mt-2 text-center text-ink-2">{{ t('sections.stage') }} ↑</p>
                </figure>

                <div class="mt-8 grid gap-6 sm:grid-cols-2">
                    <div>
                        <h3 class="t-label text-ink-2">{{ t('sections.whyExhibit') }}</h3>
                        <ul class="mt-3 space-y-2"><li v-for="w in exhibition.why" :key="w" class="border-t border-line-light pt-2">{{ w }}</li></ul>
                    </div>
                    <div>
                        <h3 class="t-label text-ink-2">{{ t('sections.whoExhibit') }}</h3>
                        <ul class="mt-3 space-y-2"><li v-for="w in exhibition.who" :key="w" class="border-t border-line-light pt-2">{{ w }}</li></ul>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-2">
                    <CtaLink :href="cta('exhibit').href" tone="green">{{ cta('exhibit').label }}</CtaLink>
                    <button type="button" class="btn btn-outline" aria-haspopup="dialog" @click="enquiring = true">{{ t('sections.enquire') }}</button>
                </div>
            </div>
        </div>

        <!-- Sponsorship -->
        <div class="on-light flow section sponsor-side">
            <div class="mx-auto max-w-[660px] px-4 md:px-8 lg:pl-16">
                <p class="t-label text-gold-ink">{{ t('sections.sponsorship') }}</p>
                <SplitHeading :text="headings.sponsorship.title" :emphasis="headings.sponsorship.emphasis" emphasis-class="text-gold-ink" class="t-h2 mt-3" />
                <h3 class="t-label mt-8 text-ink-2">{{ t('sections.tiers') }}<span v-if="sponsorship.provisional"> · {{ t('common.provisional') }}</span></h3>
                <ol class="mt-3 border-t border-line-light">
                    <li v-for="(tier, i) in sponsorship.tiers" :key="tier" class="border-b border-line-light" data-reveal="fade">
                        <Link :href="withUtm(cta('sponsor').href, utm)" class="group flex items-baseline gap-4 py-3 transition-colors duration-300 hover:bg-snow/60 focus-visible:bg-snow/60">
                            <span class="t-label w-6 text-ink-2">{{ String(i + 1).padStart(2, '0') }}</span>
                            <span class="font-display font-black uppercase transition-colors duration-300 group-hover:text-gold-ink group-focus-visible:text-gold-ink" :class="i === 0 ? 'text-4xl text-gold-ink' : i < 4 ? 'text-3xl' : 'text-2xl text-ink-2'">{{ tier }}</span>
                            <Icon name="arrow" class="mr-2 ml-auto -translate-x-2 self-center text-gold-ink opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 group-focus-visible:translate-x-0 group-focus-visible:opacity-100" />
                        </Link>
                    </li>
                </ol>
                <div class="mt-8 flex flex-wrap gap-2">
                    <CtaLink :href="cta('sponsor').href" tone="red">{{ cta('sponsor').label }}</CtaLink>
                    <button type="button" class="btn btn-ghost" disabled><Icon name="download" :size="16" /> {{ t('sections.prospectus') }} · {{ t('common.comingSoon') }}</button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="enquiring" :label="t('sections.enquire')" variant="modal" tone="light">
            <template #title><h2 class="font-display text-3xl font-black uppercase">{{ t('sections.enquire') }}</h2></template>
            <EnquiryForm />
        </Dialog>
    </section>
</template>

<style scoped>
@media (min-width: 1024px) {
    .expo-side {
        clip-path: polygon(0 0, 100% 0, calc(100% - 96px) 100%, 0 100%);
        margin-right: -96px;
        position: relative;
        z-index: 1;
    }
    .sponsor-side {
        padding-left: 96px;
    }
}
</style>
