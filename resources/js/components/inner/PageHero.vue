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
 * Compact inner-page hero on a dark ground (the transparent header needs it).
 * text: night band · photo: photograph under a night gradient · facts: adds the date / venue / city strip.
 */
const props = withDefaults(
    defineProps<{ title: string; emphasis?: string; eyebrow?: string; intro?: string; crumbs?: Crumb[]; variant?: 'text' | 'photo' | 'facts'; image?: string | null; imagePosition?: string }>(),
    { variant: 'text', crumbs: () => [], image: null, imagePosition: 'center' },
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
    <section ref="root" class="grain relative isolate overflow-hidden bg-night text-paper" :class="photo ? 'min-h-[30rem] md:min-h-[36rem]' : ''">
        <template v-if="photo">
            <Photo :src="image" eager :position="imagePosition" class="absolute! inset-0 -z-20" />
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/80 to-night/30" aria-hidden="true" />
        </template>
        <div class="container-site flex flex-col pt-[calc(var(--header-h)+2.5rem)] pb-12 md:pb-16" :class="photo && 'min-h-[inherit] justify-end'">
            <Breadcrumbs v-if="crumbs.length" :items="crumbs" class="text-paper/80" />
            <p v-if="eyebrow" class="t-label mt-8 text-gold" data-reveal="fade">{{ eyebrow }}</p>
            <SplitHeading :text="title" :emphasis="emphasis" emphasis-class="text-gold" as="h1" class="t-h2-xl max-w-5xl" :class="eyebrow ? 'mt-3' : 'mt-8'" />
            <p v-if="intro" class="mt-6 max-w-2xl text-lg text-paper/85" data-reveal="fade">{{ intro }}</p>
            <div v-if="$slots.actions" class="mt-8 flex flex-wrap gap-3" data-reveal="fade"><slot name="actions" /></div>
        </div>
        <div v-if="variant === 'facts'" class="border-t border-line-dark bg-night/70 backdrop-blur-sm">
            <dl class="container-site grid grid-cols-1 sm:grid-cols-3">
                <div v-for="f in facts" :key="f.label" class="border-line-dark py-5 sm:border-r sm:px-6 sm:first:pl-0 sm:last:border-r-0">
                    <dt class="t-label text-paper/60">{{ f.label }}</dt>
                    <dd class="mt-1 text-lg font-semibold" :class="f.tbc && 'text-paper/80'">{{ f.value }}</dd>
                </div>
            </dl>
        </div>
    </section>
</template>
