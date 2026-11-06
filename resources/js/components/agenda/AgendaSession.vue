<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import type { AgendaSession } from '@/types/inner';

// One session card of the programme list (after takeoffistanbul.com/en/schedule): time, title linking to the session page,
// hall, summary, hashtags, the speaker, then View Details and Add to Calendar.
const props = defineProps<{ session: AgendaSession; icon: GlyphName; tone: string; calendar: string }>();
const { t } = useI18n();

const time = computed(() => props.session.time.replace('–', ' – '));
const minutes = computed(() => {
    const [from, to] = props.session.time.split('–').map((p) => p.trim().split(':').map(Number));
    return from && to ? to[0] * 60 + to[1] - (from[0] * 60 + from[1]) : 0;
});
const tags = computed(() => [...new Set([props.session.type, props.session.track].filter(Boolean))]);
</script>

<template>
    <li class="ag-card in-card is-solid gap-4 p-5 md:p-7" :class="tone">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="font-display text-2xl leading-tight font-extrabold text-green md:text-3xl">{{ time }}</p>
            <span class="in-pill px-3.5! py-1.5! text-sm!"><Glyph :name="icon" class="size-4.5" />{{ session.type }}</span>
        </div>
        <h3 class="text-lg leading-tight font-bold text-night uppercase md:text-2xl">
            <Link :href="session.href" class="transition-colors hover:text-green">{{ session.title }}</Link>
        </h3>
        <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm font-semibold text-night md:text-base">
            <span class="flex items-center gap-2"><Glyph name="pin" class="size-4.5 text-green" />{{ session.hall }}</span>
            <span class="flex items-center gap-2 text-ink-2"><Glyph name="calendar" class="size-4.5 text-green" />{{ session.day }}</span>
            <span v-if="minutes" class="flex items-center gap-2 text-ink-2"><Glyph name="clock" class="size-4.5 text-green" />{{ t('inner.agenda.minutes', { n: minutes }) }}</span>
        </p>
        <p class="text-base leading-snug text-ink-2 md:text-lg">{{ session.summary ?? '…' }}</p>
        <ul v-if="tags.length" class="flex flex-wrap gap-2">
            <li v-for="tag in tags" :key="tag" class="rounded-md bg-green/10 px-3 py-1 text-sm font-semibold text-green">#{{ tag }}</li>
        </ul>

        <div class="mt-1">
            <h4 class="text-sm font-semibold text-green">{{ t('inner.agenda.speaker') }}</h4>
            <div class="mt-3 flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-green/10 text-green"><Glyph name="user" class="size-6" /></span>
                <span class="min-w-0">
                    <span class="block text-sm font-bold text-night uppercase sm:text-base">{{ session.speaker ?? t('sections.speakerTba') }}</span>
                    <span v-if="session.track" class="block text-sm text-ink-2">{{ session.track }}</span>
                </span>
            </div>
        </div>

        <div class="mt-2 flex flex-wrap items-center gap-3 border-t border-night/10 pt-5">
            <InBtn :href="session.href" tone="gold" class="min-h-0! gap-2! px-5! py-2.5! text-sm!" :aria-label="`${t('inner.agenda.viewDetails')}: ${session.title}`">{{ t('inner.agenda.viewDetails') }}</InBtn>
            <a :href="calendar" target="_blank" rel="noopener" class="in-btn is-ghost min-h-0! gap-2! px-5! py-2.5! text-sm!" :aria-label="`${t('inner.agenda.addCalendar')}: ${session.title}`"><Glyph name="calendar" class="size-4.5" />{{ t('inner.agenda.addCalendar') }}</a>
        </div>
    </li>
</template>

<style scoped>
.ag-card {
    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease;
}
.ag-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 24px 48px -32px rgb(7 20 17 / 0.45);
}
</style>
