<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import TextField from '@/components/forms/TextField.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import ListLayout from '@/components/inner/ListLayout.vue';
import SearchField from '@/components/inner/SearchField.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import { useSubmitted } from '@/components/inner/useSubmitted';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import RollingNumber from '@/components/ui/RollingNumber.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, Option, Paginated, ProgramCard, SectionHeading } from '@/types/inner';

// Brief §15: divisional summits, district roadshows and campus programs share this directory page (one per type):
// progress counter, what happens at each programme, the 8 division cards (divisional), the searchable directory and,
// on campus programs, the ambassador lead form (AMB reference).
const props = defineProps<{
    page: InnerPage;
    content: {
        type: string;
        typeLabel: string;
        filters: { q: string; division: string; status: string };
        programs: Paginated<ProgramCard>;
        divisionOptions: (Option & { lat: number; lng: number })[];
        statusOptions: Option[];
        divisions: { name: string; slug: string; program: ProgramCard | null }[] | null;
        counter: { label: string; value: number | null; of: number | null };
        steps: { title: string; tag: string; href: string; text: string }[];
        activities: { icon: GlyphName; title: string; text: string }[];
        includes: string[];
        headings: Record<'activities' | 'divisions' | 'directory' | 'ambassador', SectionHeading>;
        empty: { title: string; body: string };
        ambassador: { text: string; points: string[]; consent: Consent } | null;
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const { filters, loading, filtered, go, clear } = useDirectory({ ...props.content.filters }, { only: ['content'], anchor: '#directory' });
const tones = ['tone-teal', 'tone-sun', 'tone-sky', 'tone-violet', 'tone-mint', 'tone-rose', 'tone-blue', 'tone-sun'];
const date = (iso: string | null) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.toBeConfirmed'));
const here = computed(() => props.page.path);

const form = useForm({ institution: '', name: '', email: '', mobile: '', department: '', motivation: '', consent: false, hp_company: '' });
const { reference, reveal } = useSubmitted('ambassador', '#ambassador');
const submit = () => form.post('/campus-programs/ambassadors', { preserveScroll: true, onSuccess: () => (form.reset(), reveal()) });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page">
        <template #below>
            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:mt-10 lg:grid-cols-4">
                <Link v-for="(s, i) in content.steps" :key="s.href" :href="s.href" class="in-card is-washed is-link flex-row! items-center gap-4 p-4" :class="[tones[i], s.href === here && 'ring-2 ring-[var(--tint)]']" :aria-current="s.href === here ? 'page' : undefined" data-reveal="fade">
                    <span class="in-num text-2xl!">{{ String(i + 1).padStart(2, '0') }}</span>
                    <span class="min-w-0">
                        <span class="block font-bold text-night">{{ s.title }}</span>
                        <span class="in-meta">{{ s.tag }}</span>
                    </span>
                </Link>
            </div>
        </template>
    </InnerHero>

    <div ref="root">
        <!-- What happens at each programme + progress counter -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.activities">
                    <div class="in-card tone-teal is-washed flex-row! items-center gap-4 px-5 py-3">
                        <span class="in-num"><RollingNumber v-if="content.counter.value !== null" :value="content.counter.value" /><template v-else>0</template></span>
                        <span class="text-sm leading-tight font-semibold text-ink-2">{{ content.counter.label }}<template v-if="content.counter.of"><br />of {{ content.counter.of }}</template></span>
                    </div>
                </SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(a, i) in content.activities" :key="a.title" data-reveal="fade"><TintCard :title="a.title" :text="a.text" :icon="a.icon" :tone="tones[i]" /></li>
                </ul>
                <ul class="mt-6 flex flex-wrap gap-2" aria-label="Every programme page includes">
                    <li v-for="inc in content.includes" :key="inc" class="in-pill bg-white/70!">{{ inc }}</li>
                </ul>
            </div>
        </section>

        <!-- Divisional: all 8 divisions, scheduled or not -->
        <section v-if="content.divisions" class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.divisions" />
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(d, i) in content.divisions" :key="d.slug" data-reveal="fade">
                        <article class="in-card is-washed in-pad h-full" :class="[tones[i], d.program && 'is-link']">
                            <span class="in-meta">Division {{ String(i + 1).padStart(2, '0') }}</span>
                            <h3 class="in-h3 mt-2 text-2xl!">
                                <Link v-if="d.program" :href="d.program.href" class="after:absolute after:inset-0">{{ d.name }}</Link>
                                <template v-else>{{ d.name }}</template>
                            </h3>
                            <p class="mt-2 text-[0.9375rem] text-ink-2">{{ d.program ? `${date(d.program.startsAt)} · ${d.program.statusLabel}` : 'Date and venue announced soon' }}</p>
                            <span v-if="d.program" class="in-arrow mt-5" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
                        </article>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Directory -->
        <section id="directory" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.directory" />
                <ListLayout bare :meta="content.programs.meta" :count="content.programs.data.length" :loading="loading" :filtered="filtered" :empty-title="content.empty.title" :empty-body="content.empty.body" @page="go" @clear="clear">
                    <template #toolbar>
                        <SearchField v-model="filters.q" class="lg:w-72" :placeholder="`Search ${content.typeLabel.toLowerCase()}s`" />
                        <FilterChips v-model="filters.division" :options="content.divisionOptions" label="Division" class="min-w-0 flex-1 overflow-x-auto" />
                    </template>
                    <InfoCard
                        v-for="p in content.programs.data"
                        :key="p.slug"
                        :title="p.title"
                        :href="p.href"
                        :image="p.image ?? '/media/photos/gallery-0' + ((p.slug.length % 9) + 1) + '.jpg'"
                        :meta="[date(p.startsAt), p.location ?? p.district ?? p.division]"
                        :status="{ value: p.status, label: p.statusLabel }"
                        :sample="p.sample"
                        :text="p.partner ? `With ${p.partner}` : null"
                    />
                    <template #empty><Link href="/register" class="in-btn">Register for the Grand Summit</Link></template>
                </ListLayout>
            </div>
        </section>

        <!-- Campus ambassadors -->
        <section v-if="content.ambassador" id="ambassador" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-5">
                    <SectionHead :heading="content.headings.ambassador" />
                    <p class="in-lead -mt-4" data-reveal="fade">{{ content.ambassador.text }}</p>
                    <ul class="in-ticks mt-6 text-ink-2">
                        <li v-for="pt in content.ambassador.points" :key="pt">{{ pt }}</li>
                    </ul>
                </div>
                <div class="lg:col-span-7">
                    <FormSuccess v-if="reference" title="Thank you" :reference="reference" reference-label="Your reference number" body="The campus team will contact you when programmes are scheduled for your institution." />
                    <div v-else class="in-form">
                        <StepShell :form="form" submit-label="Send my details" @submit="submit">
                            <div class="grid gap-5 sm:grid-cols-2">
                                <TextField v-model="form.institution" label="University, polytechnic or institution" name="institution" required :error="form.errors.institution" class="sm:col-span-2" />
                                <TextField v-model="form.name" label="Full name" name="name" required autocomplete="name" :error="form.errors.name" />
                                <TextField v-model="form.department" label="Department" name="department" :error="form.errors.department" />
                                <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                                <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                                <TextareaField v-model="form.motivation" label="Why your campus?" name="motivation" :max="1000" :rows="3" :error="form.errors.motivation" class="sm:col-span-2" />
                                <ConsentField v-model="form.consent" :consent="content.ambassador.consent" :error="form.errors.consent" class="sm:col-span-2" />
                            </div>
                        </StepShell>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <GetInvolved />
</template>
