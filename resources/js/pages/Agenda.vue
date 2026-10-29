<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaBand from '@/components/inner/CtaBand.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import PageHero from '@/components/inner/PageHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TbcNotice from '@/components/inner/TbcNotice.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
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
const filters = computed(() => [
    { label: t('inner.list.day'), model: day, options: days.value },
    { label: t('inner.list.type'), model: type, options: types.value },
    { label: t('inner.list.hall'), model: hall, options: halls.value },
]);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <PageHero :eyebrow="page.hero.eyebrow" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" tone="light" :crumbs="page.crumbs">
        <template #actions>
            <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
        </template>
    </PageHero>

    <div ref="root" class="on-light">
        <!-- Programme: filters + session list -->
        <section class="section bg-snow" aria-labelledby="ag-programme">
            <div class="container-site">
                <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7"><SectionHead id="ag-programme" :heading="content.headings.programme" /></div>
                    <div class="lg:col-span-5"><TbcNotice /></div>
                </div>

                <div class="mt-12 grid gap-6 border-y border-line-light py-6 lg:grid-cols-[8rem_1fr]">
                    <template v-for="f in filters" :key="f.label">
                        <p class="t-label pt-3 text-ink-2" aria-hidden="true">{{ f.label }}</p>
                        <FilterChips v-model="f.model.value" :options="f.options" :label="f.label" />
                    </template>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <p class="t-label text-ink-2" aria-live="polite">
                        <span v-if="content.agenda.sample" class="mr-2 bg-gold px-2 py-1 text-night">{{ t('common.sample') }}</span>{{ t('inner.list.results', rows.length) }}
                    </p>
                    <button v-if="filtered" type="button" class="link-u t-label min-h-11 text-green" @click="clear">{{ t('inner.list.clear') }}</button>
                </div>

                <ol v-if="rows.length" class="mt-4 border-t border-night">
                    <li v-for="s in rows" :key="s.ics" class="grid grid-cols-[5.5rem_1fr] gap-x-4 gap-y-3 border-b border-line-light py-6 md:grid-cols-[8rem_1fr_14rem_auto] md:items-center md:gap-6">
                        <div>
                            <p class="font-display text-2xl leading-none font-black uppercase">{{ s.day }}</p>
                            <p class="t-label mt-2 text-ink-2">{{ s.time }}</p>
                        </div>
                        <div>
                            <span class="t-label border border-green px-2 py-1 text-green">{{ s.type }}</span>
                            <h3 class="mt-3 text-xl font-bold">{{ s.title }}</h3>
                            <p class="mt-1 text-ink-2">{{ s.hall }}</p>
                        </div>
                        <p class="col-start-2 text-sm text-ink-2 md:col-start-auto">{{ s.speaker ?? t('sections.speakerTba') }}</p>
                        <a :href="s.ics" class="link-u t-label col-start-2 inline-flex min-h-11 items-center gap-2 justify-self-start md:col-start-auto md:justify-self-end" :aria-label="`${t('sections.addToCalendar')}: ${s.title}`"><Icon name="calendar" :size="16" /> {{ t('sections.addToCalendar') }}</a>
                    </li>
                </ol>
                <EmptyState v-else class="mt-6" level="h3" :title="t('inner.empty.title')" :body="t('inner.list.noMatch')">
                    <button type="button" class="btn btn-outline [clip-path:none]" @click="clear">{{ t('inner.list.clear') }}</button>
                </EmptyState>
            </div>
        </section>

        <!-- Session formats (brief §14 session types, grouped) -->
        <section class="section" aria-labelledby="ag-formats">
            <div class="container-site">
                <SectionHead id="ag-formats" :heading="content.headings.formats" />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(f, i) in content.formats" :key="f.title" class="card p-6" data-reveal="fade">
                        <p class="font-display text-4xl leading-none font-black text-green">{{ String(i + 1).padStart(2, '0') }}</p>
                        <h3 class="mt-6 text-xl font-semibold">{{ f.title }}</h3>
                        <p class="mt-2 text-ink-2">{{ f.text }}</p>
                    </li>
                </ul>
                <ul class="mt-10 flex flex-wrap gap-2" :aria-label="content.headings.formats.eyebrow">
                    <li v-for="ty in content.agenda.types" :key="ty" class="t-label border border-line-light bg-snow px-3 py-2">{{ ty }}</li>
                </ul>
            </div>
        </section>

        <!-- Downloads (agenda, brochure): enabled once the files are uploaded -->
        <section class="section bg-mist" aria-labelledby="ag-downloads">
            <div class="container-site grid gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-5"><SectionHead id="ag-downloads" :heading="content.headings.downloads" /></div>
                <ul class="grid gap-4 sm:grid-cols-2 lg:col-span-7">
                    <li v-for="d in content.downloads" :key="d.kind" data-reveal="fade"><DownloadCard :item="d" /></li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
    </CtaBand>
</template>
