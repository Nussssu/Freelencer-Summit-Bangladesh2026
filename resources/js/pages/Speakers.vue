<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/inner/EmptyState.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import TintCard from '@/components/inner/TintCard.vue';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import PersonCard from '@/components/inner/PersonCard.vue';
import SearchField from '@/components/inner/SearchField.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, SpeakersContent } from '@/types/inner';

// Brief §13: speaker directory with search and filters (role, country, expertise), 3-up photo cards linking to profiles.
// A filter row only appears once the data has more than one value for it.
// shortcut: client-side filtering over the HomeContent list; switch to SpeakerQuery + useDirectory when speakers move to the CMS.
const props = defineProps<{ page: InnerPage; content: SpeakersContent }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const q = ref('');
const role = ref('');
const country = ref('');
const expertise = ref('');
const opts = (values: (string | null)[]) => [...new Set(values.filter((v): v is string => !!v))].map((v) => ({ value: v, label: v }));
const filters = computed(() =>
    [
        { label: t('inner.list.role'), model: role, options: opts(props.content.speakers.map((s) => s.role)) },
        { label: t('inner.list.country'), model: country, options: opts(props.content.speakers.map((s) => s.country)) },
        { label: 'Expertise', model: expertise, options: opts(props.content.speakers.flatMap((s) => s.expertise)) },
    ].filter((f) => f.options.length > 1),
);
const people = computed(() => {
    const needle = q.value.trim().toLowerCase();
    return props.content.speakers.filter(
        (s) =>
            (!needle || [s.name, s.designation, s.organisation].join(' ').toLowerCase().includes(needle)) &&
            (!role.value || s.role === role.value) &&
            (!country.value || s.country === country.value) &&
            (!expertise.value || s.expertise.includes(expertise.value)),
    );
});
const filtered = computed(() => !!(q.value || role.value || country.value || expertise.value));
const tones = ['tone-teal', 'tone-sun', 'tone-sky', 'tone-violet', 'tone-mint', 'tone-rose'];
const catIcons = ['globe', 'cap', 'star', 'rocket', 'flag', 'store'] as const;
const clear = () => (q.value = role.value = country.value = expertise.value = '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!" aria-labelledby="sp-directory">
            <div class="container-site">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7"><SectionHead id="sp-directory" :heading="content.headings.directory" /></div>
                    <SearchField v-model="q" class="lg:col-span-5 lg:mb-11" placeholder="Search by name or organisation" />
                </div>
                <div v-if="filters.length" class="in-card grid gap-4 p-4 md:p-5 lg:grid-cols-[7rem_1fr] lg:items-center">
                    <template v-for="f in filters" :key="f.label">
                        <p class="in-meta" aria-hidden="true">{{ f.label }}</p>
                        <FilterChips v-model="f.model.value" :options="f.options" :label="f.label" class="min-w-0 overflow-x-auto" />
                    </template>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <p class="in-meta" aria-live="polite">{{ t('inner.list.results', people.length) }}</p>
                    <button v-if="filtered" type="button" class="in-link min-h-0! text-sm" @click="clear">{{ t('inner.list.clear') }}</button>
                </div>
                <ul v-if="people.length" class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <li v-for="p in people" :key="p.slug" data-reveal="fade"><PersonCard :person="p" heading-level="h3" /></li>
                </ul>
                <EmptyState v-else class="mt-4" level="h3" icon="target" :body="t('inner.list.noMatch')">
                    <button type="button" class="in-btn is-ghost" @click="clear">{{ t('inner.list.clear') }}</button>
                </EmptyState>
            </div>
        </section>

        <!-- Who speaks (brief §13 speaker categories) -->
        <section class="in-section" aria-labelledby="sp-categories">
            <div class="container-site">
                <SectionHead id="sp-categories" :heading="content.headings.categories" />
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(c, i) in content.categories" :key="c" data-reveal="fade"><TintCard :title="c" :icon="catIcons[i % 6]" :tone="tones[i % 6]" layout="row" /></li>
                </ul>
            </div>
        </section>
    </div>

    <GetInvolved />
</template>
