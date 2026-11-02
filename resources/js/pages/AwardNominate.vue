<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import ErrorSummary from '@/components/forms/ErrorSummary.vue';
import Field from '@/components/forms/Field.vue';
import FileField from '@/components/forms/FileField.vue';
import FormNav from '@/components/forms/FormNav.vue';
import FormPage from '@/components/forms/FormPage.vue';
import FormSteps from '@/components/forms/FormSteps.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import { iconInputClass } from '@/components/forms/input';
import LinksField from '@/components/forms/LinksField.vue';
import RadioCards from '@/components/forms/RadioCards.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import { useFormDraft } from '@/components/forms/useFormDraft';
import Glyph from '@/components/inner/Glyph.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import type { Consent, InnerPage, LinkRow, Option, OptionGroup } from '@/types/inner';

// Brief §9 nomination, separate from registration (AWD reference), laid out like the client mock-up (FormPage): title block
// with the trophy, then one white card with a five-step progress bar (profile, category, evidence, documents, review),
// Save as draft and a gold Next button. Every step is validated here first; the server validates everything again.
const props = defineProps<{
    page: InnerPage;
    content: { categories: Option[]; provisional: boolean; districts: OptionGroup[]; platforms: Option[]; experience: Option[]; consent: Consent; declaration: string; steps: string[] };
}>();
const { t } = useI18n();

const step = ref(0);
const form = useForm({
    nominee_name: '', nominee_email: '', nominee_mobile: '', district: '', headline: '', experience: '', bio: '',
    category: '',
    self_nominated: '1',
    nominator_name: '', nominator_email: '', nominator_mobile: '',
    achievements: '',
    portfolio: [{ url: '' }] as LinkRow[],
    profiles: [{ platform: '', url: '' }] as LinkRow[],
    files: [] as File[],
    declaration: false, consent: false, hp_company: '',
});
const draft = useFormDraft('nominate', form, ['files', 'declaration', 'consent', 'hp_company']);
const { reference, reveal } = useSubmitted('nominate');
// "Nominate" links on the categories page preselect a category (?category=slug), read after hydration.
onMounted(() => {
    const preset = new URLSearchParams(location.search).get('category') ?? '';
    if (props.content.categories.some((c) => c.value === preset)) form.category = preset;
});
const self = computed(() => form.self_nominated === '1');
const who = [
    { value: '1', label: 'Myself', description: 'I am applying for my own work.' },
    { value: '0', label: 'Someone else', description: 'I am nominating another freelancer, with their agreement.' },
];
const category = computed(() => props.content.categories.find((c) => c.value === form.category)?.label);

const intro = [
    { icon: 'trophy' as const, text: 'Recognize Talent' },
    { icon: 'people' as const, text: 'Inspire the Community' },
    { icon: 'rocket' as const, text: 'Celebrate Impact' },
];
const heads = [
    'Provide your basic information and professional background.',
    'Choose the award and tell us who is making the nomination.',
    'Show the jury your work, results and marketplace profiles.',
    'Add certificates, client letters or other proof of your work.',
    'Check the details, then confirm and submit.',
];

type TextKey = 'nominee_name' | 'nominee_email' | 'nominee_mobile' | 'headline' | 'nominator_name' | 'nominator_email' | 'nominator_mobile';
type TextField = { key: TextKey; label: string; icon: GlyphName; placeholder: string; type?: string; autocomplete?: string };
const profile: TextField[] = [
    { key: 'nominee_name', label: 'Full Name', icon: 'user', placeholder: 'Enter your full name', autocomplete: 'name' },
    { key: 'nominee_email', label: 'Email Address', icon: 'mail', placeholder: 'Enter your email address', type: 'email', autocomplete: 'email' },
    { key: 'nominee_mobile', label: 'Mobile Number', icon: 'phone', placeholder: '01XXXXXXXXX', type: 'tel', autocomplete: 'tel' },
];
const headline: TextField = { key: 'headline', label: 'Professional Title / Headline', icon: 'briefcase', placeholder: 'e.g. Freelance Graphic Designer, Web Developer', autocomplete: 'organization-title' };
const nominator: TextField[] = [
    { key: 'nominator_name', label: 'Your Name', icon: 'user', placeholder: 'Enter your full name', autocomplete: 'name' },
    { key: 'nominator_email', label: 'Your Email', icon: 'mail', placeholder: 'Enter your email address', type: 'email', autocomplete: 'email' },
    { key: 'nominator_mobile', label: 'Your Mobile Number', icon: 'phone', placeholder: '01XXXXXXXXX', type: 'tel', autocomplete: 'tel' },
];

const checks: Record<number, () => Record<string, string | false>> = {
    0: () => ({
        nominee_name: !form.nominee_name.trim() && 'Enter your full name.',
        nominee_email: !form.nominee_email.trim() && 'Enter your email address.',
        nominee_mobile: !form.nominee_mobile.trim() && 'Enter your mobile number.',
        district: !form.district && 'Choose your city.',
        headline: !form.headline.trim() && 'Enter your professional title.',
        experience: !form.experience && 'Choose your years of freelancing experience.',
        bio: !form.bio.trim() && 'Write a short bio.',
    }),
    1: () => ({
        category: !form.category && 'Choose an award category.',
        nominator_name: !self.value && !form.nominator_name.trim() && 'Enter your name.',
        nominator_email: !self.value && !form.nominator_email.trim() && 'Enter your email.',
        nominator_mobile: !self.value && !form.nominator_mobile.trim() && 'Enter your mobile number.',
    }),
    2: () => ({ achievements: !form.achievements.trim() && 'Describe the achievements for the jury.' }),
};
function next() {
    form.clearErrors();
    const found = Object.entries(checks[step.value]?.() ?? {}).filter((e): e is [string, string] => !!e[1]);
    found.forEach(([k, v]) => form.setError(k as 'category', v));
    if (!found.length) step.value++;
}
const fieldStep = (k: string) => (/^(nominee|district|headline|experience|bio)/.test(k) ? 0 : /^(category|self_nominated|nominator)/.test(k) ? 1 : /^(achievements|portfolio|profiles)/.test(k) ? 2 : /^files/.test(k) ? 3 : 4);
const submit = () =>
    form
        .transform((d) => ({ ...d, self_nominated: d.self_nominated === '1' }))
        .post('/awards/nominate', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
            onError: (e) => (step.value = Math.min(...Object.keys(e).map(fieldStep))),
        });
const last = computed(() => step.value === props.content.steps.length - 1);

const saved = ref('');
function saveDraft() {
    saved.value = draft.save() ? 'Draft saved on this device. Files are not saved; add them again before you submit.' : 'Your browser blocked saving a draft.';
}
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <FormPage eyebrow="Freelancer Awards" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" :points="intro" :image="page.hero.image" label="Nomination form">
        <FormSuccess v-if="reference" title="Nomination submitted" :reference="reference" reference-label="Your application reference" body="Status: Submitted. We have emailed a confirmation. Shortlisted nominees are announced on the awards page.">
            <InBtn href="/awards">Back to the awards</InBtn>
            <InBtn href="/register" tone="ghost">Register for the summit</InBtn>
        </FormSuccess>

        <form v-else novalidate @submit.prevent="last ? submit() : next()">
            <FormSteps v-model="step" :steps="content.steps" />

            <h2 class="mt-8 text-2xl font-bold text-night md:text-[1.75rem]">{{ step + 1 }}. {{ step === 0 ? 'Applicant / Nominee Profile' : content.steps[step] }}</h2>
            <p class="mt-1 text-ink-2 md:text-lg">{{ heads[step] }}</p>

            <ErrorSummary :errors="form.errors" class="mt-6" />
            <!-- Honeypot: humans never see or fill it (App\Http\Middleware\Honeypot). -->
            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label>{{ t('inner.form.honeypot') }} <input v-model="form.hp_company" type="text" name="hp_company" tabindex="-1" autocomplete="off" /></label>
            </div>

            <!-- 1. Applicant profile -->
            <div v-show="step === 0" class="nom-fields mt-7 grid gap-x-9 gap-y-6 sm:grid-cols-2">
                <Field v-for="f in profile" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                    <div class="relative">
                        <Glyph :name="f.icon" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <input :id="id" v-model.trim="form[f.key]" :type="f.type ?? 'text'" :name="f.key" :inputmode="f.type === 'tel' ? 'tel' : undefined" :autocomplete="f.autocomplete" :placeholder="f.placeholder" required :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" label="Current Location (City)" name="district" required :error="form.errors.district">
                    <div class="relative">
                        <Glyph name="pin" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <select :id="id" v-model="form.district" name="district" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.district && 'text-ink-2/80']">
                            <option value="">Select your city</option>
                            <optgroup v-for="g in content.districts" :key="g.label" :label="g.label">
                                <option v-for="o in g.options" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                            </optgroup>
                        </select>
                        <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" :label="headline.label" name="headline" required :error="form.errors.headline">
                    <div class="relative">
                        <Glyph :name="headline.icon" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <input :id="id" v-model.trim="form.headline" type="text" name="headline" maxlength="160" :autocomplete="headline.autocomplete" :placeholder="headline.placeholder" required :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" label="Years of Freelancing Experience" name="experience" required :error="form.errors.experience">
                    <div class="relative">
                        <Glyph name="calendar" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <select :id="id" v-model="form.experience" name="experience" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.experience && 'text-ink-2/80']">
                            <option value="">Select experience</option>
                            <option v-for="o in content.experience" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                        </select>
                        <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" label="Short Bio / About You" name="bio" required :error="form.errors.bio" class="sm:col-span-2">
                    <textarea
                        :id="id"
                        v-model="form.bio"
                        name="bio"
                        rows="3"
                        maxlength="300"
                        required
                        placeholder="Write a brief introduction about yourself (max 300 characters)"
                        :aria-invalid="invalid"
                        :aria-describedby="describedby"
                        :class="[iconInputClass(invalid), 'min-h-24 py-3 pl-4!']"
                    />
                    <p class="mt-1.5 text-right text-sm text-ink-2 tabular-nums">{{ form.bio.length }} / 300</p>
                </Field>
            </div>

            <!-- 2. Award category -->
            <div v-show="step === 1" class="nom-fields mt-7 grid gap-7">
                <RadioCards v-model="form.category" label="Award category" name="category" :options="content.categories" required :error="form.errors.category" :help="content.provisional ? 'Categories are provisional until the award guidelines are published.' : undefined" />
                <RadioCards v-model="form.self_nominated" label="Who are you nominating?" name="self_nominated" :options="who" required :error="form.errors.self_nominated" />
                <fieldset v-if="!self" class="grid gap-x-9 gap-y-6 sm:grid-cols-2">
                    <legend class="mb-4 text-lg font-bold text-night">Your details (nominator)</legend>
                    <Field v-for="f in nominator" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                        <div class="relative">
                            <Glyph :name="f.icon" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                            <input :id="id" v-model.trim="form[f.key]" :type="f.type ?? 'text'" :name="f.key" :inputmode="f.type === 'tel' ? 'tel' : undefined" :autocomplete="f.autocomplete" :placeholder="f.placeholder" required :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                        </div>
                    </Field>
                </fieldset>
            </div>

            <!-- 3. Portfolio and achievements -->
            <div v-show="step === 2" class="nom-fields mt-7 grid gap-7">
                <TextareaField v-model="form.achievements" label="Achievements" name="achievements" :max="2000" :rows="6" required help="Clients served, results delivered, growth, awards, and any community work." :error="form.errors.achievements" />
                <LinksField v-model="form.portfolio" label="Portfolio links" name="portfolio" :errors="form.errors as Record<string, string>" />
                <LinksField v-model="form.profiles" label="Marketplace profiles" name="profiles" :platforms="content.platforms" :errors="form.errors as Record<string, string>" />
            </div>

            <!-- 4. Supporting documents -->
            <div v-show="step === 3" class="nom-fields mt-7">
                <FileField v-model="form.files" label="Supporting documents" name="files" :help="t('inner.form.fileHelp', { mb: 5, max: 5 })" :error="form.errors.files" />
            </div>

            <!-- 5. Review and submit -->
            <div v-show="step === 4" class="nom-fields mt-7 grid gap-6">
                <dl class="grid gap-4 rounded-2xl bg-[#eef7f8] p-5 sm:grid-cols-2 md:p-6">
                    <div><dt class="text-sm text-ink-2">Nominee</dt><dd class="font-bold">{{ form.nominee_name || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Professional title</dt><dd class="font-bold">{{ form.headline || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Category</dt><dd class="font-bold">{{ category ?? '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Nominated by</dt><dd class="font-bold">{{ self ? 'Self' : form.nominator_name || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Experience</dt><dd class="font-bold">{{ content.experience.find((o) => o.value === form.experience)?.label ?? '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Documents</dt><dd class="font-bold">{{ form.files.length }}</dd></div>
                </dl>
                <div data-field="declaration">
                    <label class="flex cursor-pointer gap-3 rounded-xl border bg-white p-4" :class="form.errors.declaration ? 'border-red' : 'border-night/15'">
                        <input v-model="form.declaration" type="checkbox" name="declaration" class="mt-1 size-5 shrink-0 accent-[var(--green)]" />
                        <span>{{ content.declaration }} <span class="text-red-ink" aria-hidden="true">*</span></span>
                    </label>
                    <p v-if="form.errors.declaration" class="mt-1.5 text-sm font-medium text-red-ink">{{ form.errors.declaration }}</p>
                </div>
                <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
            </div>

            <FormNav :back="step > 0" :label="last ? 'Submit Nomination' : `Next: ${content.steps[step + 1]}`" :processing="form.processing" :saved="saved" @back="step--" @save="saveDraft" />
        </form>
    </FormPage>
</template>
