<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ChipsField from '@/components/forms/ChipsField.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import FileField from '@/components/forms/FileField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import LinksField from '@/components/forms/LinksField.vue';
import RadioCards from '@/components/forms/RadioCards.vue';
import SelectField from '@/components/forms/SelectField.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import TextField from '@/components/forms/TextField.vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import ListLayout from '@/components/inner/ListLayout.vue';
import PageHero from '@/components/inner/PageHero.vue';
import PersonCard from '@/components/inner/PersonCard.vue';
import SearchField from '@/components/inner/SearchField.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import StatusBadge from '@/components/inner/StatusBadge.vue';
import SubNav from '@/components/inner/SubNav.vue';
import TbcNotice from '@/components/inner/TbcNotice.vue';
import TierCard from '@/components/inner/TierCard.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import SeoHead from '@/components/layout/SeoHead.vue';
import Accordion from '@/components/ui/Accordion.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import type { SharedProps } from '@/types/content';
import type { ArticleCard, Consent, DownloadItem, ExhibitorCard, LinkRow, Option, OptionGroup, Paginated, ProgramCard, SpeakerCard } from '@/types/inner';

// Local-only kit preview: every inner component with live data, a URL-synced directory and a validating form.
// Copy here is kit-demo text, not page content, so it is not in i18n.
const props = defineProps<{
    filters: { q: string; country: string };
    speakers: Paginated<SpeakerCard>;
    programs: ProgramCard[];
    news: ArticleCard[];
    exhibitors: ExhibitorCard[];
    downloads: DownloadItem[];
    districts: OptionGroup[];
    attendeeTypes: Option[];
    interests: Option[];
    consent: Consent;
}>();
const page = usePage<SharedProps>();
const dir = useDirectory({ ...props.filters }, { only: ['speakers', 'filters'], anchor: '#directory' });

const step = ref(0);
const form = useForm({
    name: '',
    email: '',
    mobile: '',
    district: '',
    attendee_type: '',
    interests: [] as string[],
    about: '',
    links: [{ platform: '', url: '' }] as LinkRow[],
    files: [] as File[],
    consent: false,
    hp_company: '',
});
const submit = () => form.post('/_kit/form', { preserveScroll: true, forceFormData: true });
</script>

<template>
    <SeoHead title="Inner kit · Freelancer Summit Bangladesh" description="Local preview of the inner-page kit." path="/_kit" robots="noindex" />
    <PageHero
        variant="facts"
        title="Every page piece, in one place"
        emphasis="one place"
        eyebrow="Inner page kit"
        intro="A local preview of the components inner pages are built from. Data below comes from the demo seeder and is marked Sample."
        image="/media/photos/highlight-keynote.jpg"
        :crumbs="[{ label: 'Kit' }]"
    >
        <template #actions>
            <CtaLink href="/register">Register for Summit</CtaLink>
            <CtaLink href="#forms" tone="outline">See the form kit</CtaLink>
        </template>
    </PageHero>
    <SubNav :items="[{ id: 'notices', label: 'Notices' }, { id: 'cards', label: 'Cards' }, { id: 'directory', label: 'Directory' }, { id: 'forms', label: 'Forms' }, { id: 'faq', label: 'FAQ' }]" />

    <section id="notices" class="on-light section">
        <div class="container-site grid gap-6 lg:grid-cols-2">
            <TbcNotice />
            <div class="card flex flex-wrap items-center gap-2 p-5">
                <StatusBadge v-for="s in ['available', 'reserved', 'confirmed', 'registration-open', 'upcoming', 'completed', 'shortlisted', 'winner']" :key="s" :status="s" :label="s.replace('-', ' ')" />
                <ShareButton title="Inner kit" class="ml-auto" />
            </div>
            <EmptyState title="Speakers revealing soon" body="We are confirming speakers now. Register to hear first when they are announced." class="lg:col-span-2">
                <CtaLink href="/register">Register for Summit</CtaLink>
            </EmptyState>
            <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2 lg:grid-cols-3">
                <DownloadCard v-for="d in downloads.slice(0, 3)" :key="d.kind" :item="d" />
                <DownloadCard :item="{ kind: 'demo', title: 'Example enabled file', href: '/og-image.png', size: 184000 }" />
            </div>
        </div>
    </section>

    <section id="cards" class="section border-y border-line-light bg-snow">
        <div class="container-site space-y-10">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <InfoCard v-for="p in programs" :key="p.slug" :title="p.title" :href="p.href" eyebrow="District roadshow" :meta="[p.startsAt ?? 'Date to be confirmed', p.location ?? 'Venue to be confirmed']" :status="{ value: p.status, label: p.statusLabel }" :image="p.image" />
            </div>
            <div class="grid gap-6 lg:grid-cols-3">
                <InfoCard v-for="n in news" :key="n.slug" :title="n.title" :href="n.href" :eyebrow="n.categoryLabel" :meta="[n.publishedAt?.slice(0, 10) ?? null]" :text="n.excerpt" :image="n.image" />
            </div>
            <div class="space-y-3">
                <InfoCard v-for="e in exhibitors" :key="e.slug" layout="row" :title="e.name" :href="e.href" :meta="[e.booth ? `Booth ${e.booth}` : 'Booth to be assigned']" :tags="[e.sector]" :logo="e.logo" />
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <TierCard :tier="{ slug: 'title', name: 'Title', benefits: ['Benefits to be confirmed by the organiser'], provisional: true }" :rank="1" featured><CtaLink href="/sponsorship-opportunity" tone="green">Enquire</CtaLink></TierCard>
                <TierCard :tier="{ slug: 'platinum', name: 'Platinum', benefits: [], provisional: true }" :rank="2" />
                <TierCard :tier="{ slug: 'gold', name: 'Gold', benefits: [], provisional: true }" :rank="3" />
            </div>
        </div>
    </section>

    <div id="directory">
        <ListLayout :meta="speakers.meta" :count="speakers.data.length" :loading="dir.loading.value" :filtered="dir.filtered.value" empty-title="Speakers revealing soon" @page="dir.go" @clear="dir.clear">
            <template #toolbar>
                <SearchField v-model="dir.filters.q" label="Search speakers" class="lg:w-80" />
                <FilterChips v-model="dir.filters.country" label="Country" :options="[{ value: 'BD', label: 'Bangladesh' }, { value: 'IN', label: 'India' }]" />
            </template>
            <PersonCard v-for="s in speakers.data" :key="s.slug" :person="s" />
        </ListLayout>
    </div>

    <section id="forms" class="on-light section border-t border-line-light">
        <div class="container-site grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <FormSuccess v-if="page.props.flash.success" title="Thank you" reference="FSB-REG-2026-00001" reference-label="Your reference number" :body="page.props.flash.success" />
                <StepShell v-else v-model:step="step" :form="form" :steps="['About you', 'Interests and files']" submit-label="Check the form" @next="step++" @submit="submit">
                    <div v-show="step === 0" class="grid gap-6 sm:grid-cols-2">
                        <TextField v-model="form.name" name="name" label="Full name" autocomplete="name" required :error="form.errors.name" />
                        <TextField v-model="form.email" name="email" type="email" label="Email" required :error="form.errors.email" />
                        <TextField v-model="form.mobile" name="mobile" type="mobile" label="Mobile number" required :error="form.errors.mobile" />
                        <SelectField v-model="form.district" name="district" label="City or district" :groups="districts" required :error="form.errors.district" />
                        <RadioCards v-model="form.attendee_type" name="attendee_type" label="I am attending as" :options="attendeeTypes.slice(0, 4)" required :error="form.errors.attendee_type" class="sm:col-span-2" />
                    </div>
                    <div v-show="step === 1" class="space-y-6">
                        <ChipsField v-model="form.interests" name="interests" label="What interests you?" :options="interests" :error="form.errors.interests" />
                        <TextareaField v-model="form.about" name="about" label="Anything we should know?" :max="500" :error="form.errors.about" />
                        <LinksField v-model="form.links" name="links" label="Marketplace profiles" :platforms="[{ value: 'upwork', label: 'Upwork' }, { value: 'fiverr', label: 'Fiverr' }, { value: 'other', label: 'Other' }]" :errors="form.errors" />
                        <FileField v-model="form.files" name="files" label="Supporting documents" :error="form.errors.files" />
                        <ConsentField v-model="form.consent" :consent="consent" :error="form.errors.consent" />
                    </div>
                </StepShell>
            </div>
            <aside class="space-y-6 lg:col-span-5">
                <div class="card p-6 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
                    <h2 class="text-xl font-semibold">What to expect</h2>
                    <p class="mt-3 text-ink-2">Info panels beside forms list only confirmed facts. This one shows the kit layout: form left, sticky panel right.</p>
                    <TbcNotice class="mt-6 shadow-none" />
                </div>
            </aside>
        </div>
    </section>

    <section id="faq" class="section bg-snow">
        <div class="container-site max-w-3xl">
            <h2 class="t-h2">Questions</h2>
            <Accordion class="mt-8" id-prefix="kit-faq" :items="[{ q: 'Is this real content?', a: 'No. The kit preview only shows how components look; real pages use the organiser\'s content.' }, { q: 'Where do strings live?', a: 'Kit strings are in resources/js/i18n/inner.en.ts; page copy comes from PHP.' }]" />
        </div>
    </section>

    <CtaBand title="Ready when you are" emphasis="you are" text="A calmer closing band for inner pages. One heading, one sentence, the action.">
        <CtaLink href="/register">Register for Summit</CtaLink>
    </CtaBand>
</template>
