<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaBand from '@/components/inner/CtaBand.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import PageHero from '@/components/inner/PageHero.vue';
import PersonCard from '@/components/inner/PersonCard.vue';
import SearchField from '@/components/inner/SearchField.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
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
        { label: props.content.headings.categories.eyebrow, model: expertise, options: opts(props.content.speakers.flatMap((s) => s.expertise)) },
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
const clear = () => (q.value = role.value = country.value = expertise.value = '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <PageHero :eyebrow="page.hero.eyebrow" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" tone="light" :crumbs="page.crumbs">
        <template #actions>
            <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
        </template>
    </PageHero>

    <div ref="root" class="on-light">
        <section class="section bg-snow" aria-labelledby="sp-directory">
            <div class="container-site">
                <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-7"><SectionHead id="sp-directory" :heading="content.headings.directory" /></div>
                    <SearchField v-model="q" class="lg:col-span-5" />
                </div>

                <div v-if="filters.length" class="mt-10 grid gap-6 border-y border-line-light py-6 lg:grid-cols-[8rem_1fr]">
                    <template v-for="f in filters" :key="f.label">
                        <p class="t-label pt-3 text-ink-2" aria-hidden="true">{{ f.label }}</p>
                        <FilterChips v-model="f.model.value" :options="f.options" :label="f.label" />
                    </template>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <p class="t-label text-ink-2" aria-live="polite">{{ t('inner.list.results', people.length) }}</p>
                    <button v-if="filtered" type="button" class="link-u t-label min-h-11 text-green" @click="clear">{{ t('inner.list.clear') }}</button>
                </div>

                <ul v-if="people.length" class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="p in people" :key="p.slug" data-reveal="fade"><PersonCard :person="p" heading-level="h3" /></li>
                </ul>
                <EmptyState v-else class="mt-4" level="h3" :body="t('inner.list.noMatch')">
                    <button type="button" class="btn btn-outline [clip-path:none]" @click="clear">{{ t('inner.list.clear') }}</button>
                </EmptyState>
            </div>
        </section>

        <!-- Who speaks (brief §13 speaker categories) -->
        <section class="section" aria-labelledby="sp-categories">
            <div class="container-site grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-5"><SectionHead id="sp-categories" :heading="content.headings.categories" /></div>
                <ul class="grid gap-x-6 sm:grid-cols-2 lg:col-span-7">
                    <li v-for="(c, i) in content.categories" :key="c" class="flex items-center gap-4 border-b border-line-light py-5" data-reveal="fade">
                        <span class="t-label text-green">{{ String(i + 1).padStart(2, '0') }}</span>
                        <span class="font-display text-2xl leading-none font-black uppercase">{{ c }}</span>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
    </CtaBand>
</template>
