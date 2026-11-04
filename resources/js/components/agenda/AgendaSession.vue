<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import type { AgendaSession } from '@/types/inner';

// One timeline row of the interactive agenda: time, type badge, title, hall, speaker, Google Calendar link; expands to the
// session brief with a photo, "what you'll learn" ticks and a link to the session's page.
const props = defineProps<{ session: AgendaSession; icon: GlyphName; tone: string; photo: string; open: boolean; calendar: string }>();
defineEmits<{ toggle: [] }>();
const { t } = useI18n();

const id = computed(() => `ag-session-${props.session.ics.replace(/\D/g, '')}`);
const minutes = computed(() => {
    const [from, to] = props.session.time.split('–').map((p) => p.trim().split(':').map(Number));
    return from && to ? to[0] * 60 + to[1] - (from[0] * 60 + from[1]) : 0;
});
</script>

<template>
    <li class="relative pl-9 md:pl-11" :class="tone">
        <span class="absolute top-7 left-2 size-4 rounded-full border-[3px] border-green bg-white md:left-3" aria-hidden="true" />
        <div class="rounded-2xl border transition-colors" :class="open ? 'border-green/40 bg-green/[0.04]' : 'border-transparent'">
            <!-- The whole row toggles the brief; the chevron button stays the keyboard control -->
            <div class="relative grid cursor-pointer items-center gap-3 p-4 lg:grid-cols-[8.5rem_10rem_minmax(0,1fr)_9rem_13rem_auto] lg:gap-5" @click="$emit('toggle')">
                <div class="pr-24 lg:pr-0">
                    <p class="font-bold whitespace-nowrap text-night">{{ session.time }}</p>
                    <p v-if="minutes" class="in-meta mt-0.5 flex items-center gap-1.5 text-xs!"><Glyph name="clock" class="size-3.5" />{{ t('inner.agenda.minutes', { n: minutes }) }}</p>
                </div>
                <span class="in-pill justify-self-start px-3.5! py-2! text-sm!"><Glyph :name="icon" class="size-4.5" />{{ session.type }}</span>
                <div class="min-w-0 lg:border-l lg:border-night/10 lg:pl-5">
                    <h3 class="text-lg leading-snug font-bold text-night">{{ session.title }}</h3>
                    <p v-if="session.summary" class="mt-1 text-sm text-ink-2">{{ session.summary }}</p>
                </div>
                <span class="inline-flex items-center gap-2 justify-self-start rounded-xl bg-green/10 px-3 py-2 text-sm font-semibold whitespace-nowrap text-night lg:whitespace-normal"><Glyph name="pin" class="size-4 text-green" />{{ session.hall }}</span>
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-night/5 text-ink-2"><Glyph name="user" class="size-5" /></span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold text-night">{{ session.speaker ?? t('sections.speakerTba') }}</span>
                        <span v-if="session.track" class="block truncate text-xs text-ink-2">{{ session.track }}</span>
                    </span>
                </div>
                <div class="absolute top-4 right-4 flex items-center gap-2 lg:static lg:justify-end">
                    <a :href="calendar" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-xl border border-night/10 bg-white text-green transition-colors hover:border-green" :aria-label="`${t('inner.agenda.addCalendar')}: ${session.title}`" :title="t('inner.agenda.addCalendar')" @click.stop><Glyph name="calendar" class="size-5" /></a>
                    <button type="button" class="grid size-10 place-items-center rounded-xl text-night transition-colors hover:bg-night/5" :aria-expanded="open" :aria-controls="id" :aria-label="`${t('inner.agenda.details')}: ${session.title}`" @click.stop="$emit('toggle')">
                        <svg viewBox="0 0 24 24" class="size-5 transition-transform" :class="open && 'rotate-90'" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg>
                    </button>
                </div>
            </div>
            <div v-if="open" :id="id" class="grid gap-5 border-t border-green/20 p-4 md:grid-cols-[16rem_1fr] lg:grid-cols-[17rem_1fr_1fr_auto] lg:items-center lg:gap-7">
                <div class="in-media aspect-[16/9] rounded-xl"><img :src="photo" alt="" loading="lazy" /></div>
                <div>
                    <h4 class="font-bold text-night">{{ t('inner.agenda.about') }}</h4>
                    <p class="mt-1.5 text-sm text-ink-2">{{ session.summary ?? t('inner.empty.body') }}</p>
                </div>
                <div v-if="session.learn?.length" class="lg:border-l lg:border-night/10 lg:pl-7">
                    <h4 class="font-bold text-night">{{ t('inner.agenda.learn') }}</h4>
                    <ul class="mt-2 grid gap-1.5 text-sm text-ink-2">
                        <li v-for="l in session.learn" :key="l" class="flex items-start gap-2"><Glyph name="check" class="mt-0.5 size-4 shrink-0 text-green" />{{ l }}</li>
                    </ul>
                </div>
                <InBtn :href="session.href" tone="gold" class="min-h-0! justify-self-start gap-2! px-4! py-2! text-sm! whitespace-nowrap" :aria-label="`${t('inner.agenda.viewDetails')}: ${session.title}`">{{ t('inner.agenda.viewDetails') }}</InBtn>
            </div>
        </div>
    </li>
</template>
