<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import TintCard from '@/components/inner/TintCard.vue';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TbcNotice from '@/components/inner/TbcNotice.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { AgendaContent, InnerPage } from '@/types/inner';

// Brief §7, §14: day-based agenda with filters by date, session type and hall, speakers, add to calendar, downloads.
// shortcut: filters run client-side on the HomeContent sample list; move to SessionQuery + useDirectory once sessions live in the CMS.
const props = defineProps<{ page: InnerPage; content: AgendaContent }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const opts = (values: string[]) => [...new Set(values)].map((v) => ({ value: v, label: v }));
const days = computed(() => opts(props.content.agenda.sessions.map((s) => s.day)));
const halls = computed(() => opts(props.content.agenda.sessions.map((s) => s.hall)));
const types = computed(() => opts(props.content.agenda.types));
const day = ref('');
const type = ref('');
const hall = ref('');
const rows = computed(() => props.content.agenda.sessions.filter((s) => (!day.value || s.day === day.value) && (!type.value || s.type === type.value) && (!hall.value || s.hall === hall.value)));
const filtered = computed(() => !!(day.value || type.value || hall.value));
const clear = () => (day.value = type.value = hall.value = '');
const tones = ['tone-teal', 'tone-sun', 'tone-sky', 'tone-violet'];
const formatIcons = ['mic', 'grid', 'globe', 'briefcase'] as const;
const filters = computed(() => [
    { label: t('inner.list.day'), model: day, options: days.value },
    { label: t('inner.list.type'), model: type, options: types.value },
    { label: t('inner.list.hall'), model: hall, options: halls.value },
]);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" variant="facts" />

    <div ref="root">
        <!-- Programme: filters + session list -->
        <section class="in-section" aria-labelledby="ag-programme">
            <div class="container-site">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7"><SectionHead id="ag-programme" :heading="content.headings.programme" /></div>
                    <div class="lg:col-span-5 lg:mb-11"><TbcNotice /></div>
                </div>
                <div class="in-card grid gap-4 p-4 md:p-5 lg:grid-cols-[7rem_1fr] lg:items-center">
                    <template v-for="f in filters" :key="f.label">
                        <p class="in-meta" aria-hidden="true">{{ f.label }}</p>
                        <FilterChips v-model="f.model.value" :options="f.options" :label="f.label" class="min-w-0 overflow-x-auto" />
                    </template>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <p class="in-meta flex items-center gap-2" aria-live="polite">
                        <span v-if="content.agenda.sample" class="rounded-full bg-gold px-2.5 py-0.5 text-xs font-semibold text-night">{{ t('common.sample') }}</span>{{ t('inner.list.results', rows.length) }}
                    </p>
                    <button v-if="filtered" type="button" class="in-link min-h-0! text-sm" @click="clear">{{ t('inner.list.clear') }}</button>
                </div>
                <ol v-if="rows.length" class="mt-4 grid gap-3">
                    <li v-for="(s, i) in rows" :key="s.ics" class="in-card is-solid grid! grid-cols-[5.5rem_1fr] gap-x-4 gap-y-3 p-5 md:grid-cols-[8rem_1fr_13rem_auto] md:items-center md:gap-6" :class="tones[i % 4]" data-reveal="fade">
                        <div>
                            <p class="font-bold text-night">{{ s.day }}</p>
                            <p class="in-meta mt-1">{{ s.time }}</p>
                        </div>
                        <div class="min-w-0">
                            <span class="in-pill">{{ s.type }}</span>
                            <h3 class="in-h3 mt-2 text-lg!">{{ s.title }}</h3>
                            <p class="mt-1 text-sm text-ink-2">{{ s.hall }}</p>
                        </div>
                        <p class="col-start-2 text-sm text-ink-2 md:col-start-auto">{{ s.speaker ?? t('sections.speakerTba') }}</p>
                        <a :href="s.ics" class="in-link col-start-2 justify-self-start text-sm md:col-start-auto md:justify-self-end" :aria-label="`${t('sections.addToCalendar')}: ${s.title}`"><Icon name="calendar" :size="16" /> {{ t('sections.addToCalendar') }}</a>
                    </li>
                </ol>
                <EmptyState v-else class="mt-4" level="h3" icon="target" :title="t('inner.empty.title')" :body="t('inner.list.noMatch')">
                    <button type="button" class="in-btn is-ghost" @click="clear">{{ t('inner.list.clear') }}</button>
                </EmptyState>
            </div>
        </section>

        <!-- Session formats -->
        <section class="in-section" aria-labelledby="ag-formats">
            <div class="container-site">
                <SectionHead id="ag-formats" :heading="content.headings.formats" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(f, i) in content.formats" :key="f.title" data-reveal="fade"><TintCard :title="f.title" :text="f.text" :icon="formatIcons[i % 4]" :tone="tones[i % 4]" /></li>
                </ul>
                <ul class="mt-6 flex flex-wrap gap-2" aria-label="Session types">
                    <li v-for="ty in content.agenda.types" :key="ty" class="in-pill bg-white/70!">{{ ty }}</li>
                </ul>
            </div>
        </section>

        <!-- Downloads -->
        <section class="in-section" aria-labelledby="ag-downloads">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="in-media is-round aspect-[16/10] lg:col-span-5" data-reveal="clip"><img src="/media/photos/highlight-keynote.jpg" alt="" loading="lazy" /></div>
                <div class="lg:col-span-7">
                    <SectionHead id="ag-downloads" :heading="content.headings.downloads" />
                    <ul class="grid gap-4 sm:grid-cols-2">
                        <li v-for="(d, i) in content.downloads" :key="d.kind" data-reveal="fade"><DownloadCard :item="d" :tone="tones[i % 4]" /></li>
                    </ul>
                    <div class="mt-6"><InBtn href="/speakers" tone="ghost">Meet the speakers</InBtn></div>
                </div>
            </div>
        </section>
    </div>

    <GetInvolved />
</template>
