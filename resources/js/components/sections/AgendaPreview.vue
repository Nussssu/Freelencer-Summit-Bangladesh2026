<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Agenda, Heading } from '@/types/content';

// Section 8. Rows with day, time, hall, speaker and session-type tag; type filter chips; .ics per row (server route).
const props = defineProps<{ agenda: Agenda; heading: Heading }>();
const { t } = useI18n();
const { site, summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);
const filter = ref<string | null>(null);
const rows = computed(() => props.agenda.sessions.map((s, i) => ({ ...s, i })).filter((s) => !filter.value || s.type === filter.value));
const cta = computed(() => site.value.ctas.secondary.find((c) => c.id === 'agenda')!);
</script>

<template>
    <section ref="root" data-section="8" aria-labelledby="ag-title" class="on-light flow section relative z-[1]">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="t-label text-green">{{ t('sections.agenda') }}</p>
                    <SplitHeading id="ag-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="btn btn-ghost" disabled :title="t('common.comingSoon')"><Icon name="download" :size="16" /> {{ t('sections.downloadAgenda') }} · {{ t('common.comingSoon') }}</button>
                    <CtaLink :href="cta.href">{{ cta.label }}</CtaLink>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap gap-2" role="group" :aria-label="t('sections.agenda')">
                <button type="button" class="chip" :aria-pressed="filter === null" @click="filter = null">{{ t('sections.allTypes') }}</button>
                <button v-for="type in agenda.types" :key="type" type="button" class="chip" :aria-pressed="filter === type" @click="filter = type">{{ type }}</button>
            </div>

            <p v-if="agenda.sample" class="t-label mt-6 inline-block bg-gold px-2 py-1">{{ t('common.sample') }} · {{ summit.dateConfirmed ? '' : t('sections.dateTbc') }}</p>

            <ol class="mt-4 border-t border-night" aria-live="polite">
                <li v-for="s in rows" :key="s.i" class="grid grid-cols-[4.5rem_1fr] items-center gap-x-4 gap-y-2 border-b border-line-light py-5 md:grid-cols-[7rem_10rem_1fr_12rem_auto] md:gap-6">
                    <p class="font-display text-3xl font-black uppercase">{{ s.day }}</p>
                    <p class="t-label row-start-2 text-ink-2 md:row-start-auto">{{ s.time }}<br class="hidden md:block" /><span class="md:hidden"> · </span>{{ s.hall }}</p>
                    <div class="col-start-2 row-span-2 row-start-1 md:col-start-auto md:row-span-1 md:row-start-auto">
                        <span class="t-label border border-green px-2 py-1 text-green">{{ s.type }}</span>
                        <h3 class="mt-2 text-xl font-bold">{{ s.title }}</h3>
                    </div>
                    <p class="col-start-2 text-sm text-ink-2 md:col-start-auto">{{ s.speaker ?? t('sections.speakerTba') }}</p>
                    <a :href="`/agenda/sessions/${s.i}.ics`" class="link-u t-label col-start-2 inline-flex min-h-11 items-center gap-2 justify-self-start md:col-start-auto md:justify-self-end" :aria-label="`${t('sections.addToCalendar')}: ${s.title}`"><Icon name="calendar" :size="16" /> {{ t('sections.addToCalendar') }}</a>
                </li>
                <li v-if="!rows.length" class="py-6 text-ink-2">{{ t('sections.noSessions') }}</li>
            </ol>
        </div>
    </section>
</template>

<style scoped>
.chip {
    min-height: 44px;
    padding: 8px 14px;
    border: 1px solid var(--line-light);
    font-family: var(--font-mono);
    font-size: 0.75rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    transition:
        background-color 0.3s ease-in-out,
        color 0.3s ease-in-out;
}
.chip:hover {
    border-color: var(--night);
}
.chip[aria-pressed='true'] {
    background: var(--night);
    color: var(--paper);
}
</style>
