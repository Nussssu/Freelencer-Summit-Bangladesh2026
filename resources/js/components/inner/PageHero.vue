<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Crumb } from '@/types/inner';
import Breadcrumbs from './Breadcrumbs.vue';
import Glyph from './Glyph.vue';

/**
 * Inner-page opener in the landing's language: the site blend shows through, breadcrumbs, a Host Grotesk headline
 * with the emphasis in teal, one line of intro and the actions, all inside the first screen. An optional rounded
 * photo (or the `aside` slot) sits on the right; variant "facts" adds date / venue / city as tinted cards.
 * `eyebrow` and `tone` are accepted for older callers and ignored (the landing dropped eyebrows).
 */
const props = withDefaults(
    defineProps<{ title: string; emphasis?: string; eyebrow?: string; intro?: string | null; crumbs?: Crumb[]; variant?: 'text' | 'photo' | 'facts'; tone?: 'dark' | 'light'; image?: string | null; imagePosition?: string }>(),
    { variant: 'text', tone: 'light', crumbs: () => [], image: null, imagePosition: 'center', intro: null },
);
const slots = defineSlots<{ actions?: () => unknown; aside?: () => unknown; below?: () => unknown }>();
const { t } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const facts = computed(() => [
    { icon: 'calendar' as const, tone: 'tone-teal', label: t('inner.tbc.date'), value: summit.value.dateConfirmed ? new Date(summit.value.startsAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.lateNov', { edition: summit.value.edition }) },
    { icon: 'building' as const, tone: 'tone-sun', label: t('inner.tbc.venue'), value: summit.value.venue || t('inner.tbc.toBeConfirmed') },
    { icon: 'pin' as const, tone: 'tone-sky', label: t('inner.tbc.city'), value: summit.value.city },
]);
const side = computed(() => !!props.image || !!slots.aside);
</script>

<template>
    <section ref="root" class="in-hero relative isolate overflow-hidden" data-header="light">
        <span class="in-pool -top-40 -right-40 h-[34rem] w-[34rem]" aria-hidden="true" />
        <span class="in-pool -bottom-48 -left-56 h-[30rem] w-[30rem]" aria-hidden="true" />
        <!-- shortcut: xl offset hard-codes the 2-row header (153px); see the note in docs/inner-design-kit.md (frozen app.css). -->
        <div class="container-site pt-[calc(var(--header-h)+1.75rem)] pb-[clamp(2.25rem,4.5vw,3.75rem)] xl:pt-[calc(153px+2rem)]">
            <Breadcrumbs v-if="crumbs.length" :items="crumbs" />
            <div class="mt-6 grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div :class="side ? 'lg:col-span-7' : 'lg:col-span-10'">
                    <SplitHeading :text="title" :emphasis="emphasis" emphasis-class="text-green" as="h1" class="in-h1" />
                    <p v-if="intro" class="in-lead mt-5 max-w-2xl" data-reveal="fade">{{ intro }}</p>
                    <div v-if="$slots.actions" class="mt-8 flex flex-wrap gap-3" data-reveal="fade"><slot name="actions" /></div>
                </div>
                <div v-if="image" class="lg:col-span-5" data-reveal="clip">
                    <div class="in-media is-round aspect-[16/10] shadow-[0_30px_60px_-34px_rgb(7_20_17/0.45)] lg:aspect-[4/3]">
                        <img :src="image" alt="" fetchpriority="high" :style="{ objectPosition: imagePosition }" />
                    </div>
                </div>
                <div v-else-if="$slots.aside" class="lg:col-span-5"><slot name="aside" /></div>
            </div>
            <dl v-if="variant === 'facts'" class="mt-8 grid gap-3 sm:grid-cols-3 lg:mt-10">
                <div v-for="f in facts" :key="f.label" class="in-card is-washed flex-row! items-center gap-4 p-4" :class="f.tone" data-reveal="fade">
                    <span class="in-disc w-12!"><Glyph :name="f.icon" /></span>
                    <div class="min-w-0">
                        <dt class="in-meta">{{ f.label }}</dt>
                        <dd class="mt-0.5 font-bold text-night">{{ f.value }}</dd>
                    </div>
                </div>
            </dl>
            <slot name="below" />
        </div>
    </section>
</template>
