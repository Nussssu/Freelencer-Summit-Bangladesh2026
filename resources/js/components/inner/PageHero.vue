<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Crumb } from '@/types/inner';
import Breadcrumbs from './Breadcrumbs.vue';
import Photo from './Photo.vue';

/**
 * Compact inner-page hero. tone dark: night ground; tone light: paper ground (data-header tells SiteHeader to go solid at the top).
 * text: plain band · photo: photograph under a night gradient (dark only) · facts: adds the date / venue / city strip.
 */
const props = withDefaults(
    defineProps<{ title: string; emphasis?: string; eyebrow?: string; intro?: string; crumbs?: Crumb[]; variant?: 'text' | 'photo' | 'facts'; tone?: 'dark' | 'light'; image?: string | null; imagePosition?: string }>(),
    { variant: 'text', tone: 'dark', crumbs: () => [], image: null, imagePosition: 'center' },
);
const { t } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const facts = computed(() => [
    { label: t('inner.tbc.date'), value: summit.value.dateConfirmed ? new Date(summit.value.startsAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.lateNov', { edition: summit.value.edition }), tbc: !summit.value.dateConfirmed },
    { label: t('inner.tbc.venue'), value: summit.value.venue ?? t('inner.tbc.toBeConfirmed'), tbc: !summit.value.venue },
    { label: t('inner.tbc.city'), value: summit.value.city, tbc: false },
]);
const photo = computed(() => props.variant === 'photo' || (props.variant === 'facts' && !!props.image));
</script>

<template>
    <section ref="root" class="relative isolate overflow-hidden" :class="[tone === 'light' ? 'on-light light-tokens' : 'grain bg-night text-paper', photo && 'min-h-[30rem] md:min-h-[36rem]']" :data-header="tone === 'light' ? 'light' : undefined">
        <template v-if="photo">
            <Photo :src="image" eager :position="imagePosition" class="absolute! inset-0 -z-20" />
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/80 to-night/30" aria-hidden="true" />
        </template>
        <!-- shortcut: xl offset hard-codes the 2-row header (153px) because app.css's @layer base override of --header-h loses to the unlayered :root; drop it once that is fixed (frozen file). -->
        <div class="container-site flex flex-col pt-[calc(var(--header-h)+2.5rem)] pb-12 md:pb-16 xl:pt-[calc(153px+2.5rem)]" :class="photo && 'min-h-[inherit] justify-end'">
            <Breadcrumbs v-if="crumbs.length" :items="crumbs" class="opacity-80" />
            <!-- lg: heading left, intro + actions bottom-aligned right, so the action stays in the first screen -->
            <div class="mt-8 grid gap-6 lg:grid-cols-12 lg:items-end lg:gap-x-6">
                <div class="lg:col-span-7">
                    <p v-if="eyebrow" class="t-label mb-3 text-gold" data-reveal="fade">{{ eyebrow }}</p>
                    <SplitHeading :text="title" :emphasis="emphasis" emphasis-class="text-gold" as="h1" class="t-h2-xl" />
                </div>
                <div class="lg:col-span-5 lg:pb-2">
                    <p v-if="intro" class="max-w-2xl text-lg opacity-85" data-reveal="fade">{{ intro }}</p>
                    <div v-if="$slots.actions" class="mt-8 flex flex-wrap gap-3" data-reveal="fade"><slot name="actions" /></div>
                </div>
            </div>
        </div>
        <div v-if="variant === 'facts'" class="border-t border-line-dark backdrop-blur-sm" :class="tone === 'light' ? 'bg-snow/70' : 'bg-night/70'">
            <dl class="container-site grid grid-cols-1 sm:grid-cols-3">
                <div v-for="f in facts" :key="f.label" class="border-line-dark py-5 sm:border-r sm:px-6 sm:first:pl-0 sm:last:border-r-0">
                    <dt class="t-label opacity-60">{{ f.label }}</dt>
                    <dd class="mt-1 text-lg font-semibold" :class="f.tbc && 'opacity-80'">{{ f.value }}</dd>
                </div>
            </dl>
        </div>
    </section>
</template>
