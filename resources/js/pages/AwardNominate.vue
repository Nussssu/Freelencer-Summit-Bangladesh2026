<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import FileField from '@/components/forms/FileField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import LinksField from '@/components/forms/LinksField.vue';
import RadioCards from '@/components/forms/RadioCards.vue';
import SelectField from '@/components/forms/SelectField.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import TextField from '@/components/forms/TextField.vue';
import { useFormDraft } from '@/components/forms/useFormDraft';
import FormLayout from '@/components/inner/FormLayout.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, LinkRow, Option, OptionGroup } from '@/types/inner';

// Brief §9 nomination, separate from registration (AWD reference): category, self or other, nominee, evidence, declaration.
const props = defineProps<{
    page: InnerPage;
    content: { categories: Option[]; provisional: boolean; districts: OptionGroup[]; platforms: Option[]; consent: Consent; declaration: string; steps: string[]; aside: { points: string[]; help: string }; eligibility: string };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const step = ref(0);
const form = useForm({
    category: '',
    self_nominated: '1',
    nominator_name: '', nominator_email: '', nominator_mobile: '',
    nominee_name: '', nominee_email: '', nominee_mobile: '', district: '',
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

const checks: Record<number, () => Record<string, string | undefined>> = {
    0: () => (form.category ? {} : { category: 'Choose an award category.' }),
    1: () => ({
        ...(!self.value && !form.nominator_name.trim() ? { nominator_name: 'Enter your name.' } : {}),
        ...(!self.value && !form.nominator_email.trim() ? { nominator_email: 'Enter your email.' } : {}),
        ...(!self.value && !form.nominator_mobile.trim() ? { nominator_mobile: 'Enter your mobile number.' } : {}),
        ...(!form.nominee_name.trim() ? { nominee_name: 'Enter the nominee name.' } : {}),
        ...(!form.nominee_email.trim() ? { nominee_email: 'Enter the nominee email.' } : {}),
        ...(!form.nominee_mobile.trim() ? { nominee_mobile: 'Enter the nominee mobile number.' } : {}),
        ...(!form.district ? { district: 'Choose a district.' } : {}),
    }),
    2: () => (form.achievements.trim() ? {} : { achievements: 'Describe the achievements for the jury.' }),
};
function next() {
    form.clearErrors();
    const errs = checks[step.value]?.() ?? {};
    const found = Object.entries(errs).filter((e): e is [string, string] => !!e[1]);
    found.forEach(([k, v]) => form.setError(k as 'category', v));
    if (!found.length) step.value++;
}
const fieldStep = (k: string) => (k === 'category' ? 0 : /^(nominee|nominator|district)/.test(k) ? 1 : /^(achievements|portfolio|profiles|files)/.test(k) ? 2 : 3);
const submit = () =>
    form
        .transform((d) => ({ ...d, self_nominated: d.self_nominated === '1' }))
        .post('/awards/nominate', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
            onError: (e) => (step.value = Math.min(...Object.keys(e).map(fieldStep))),
        });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <FormLayout :points="content.aside.points" icon="trophy" tone="tone-sun" title="How nomination works" :help="content.aside.help" help-href="/contact?topic=awards">
            <FormSuccess v-if="reference" title="Nomination submitted" :reference="reference" reference-label="Your application reference" body="Status: Submitted. We have emailed a confirmation. Shortlisted nominees are announced on the awards page.">
                <InBtn href="/awards">Back to the awards</InBtn>
                <InBtn href="/register" tone="ghost">Register for the summit</InBtn>
            </FormSuccess>
            <div v-else class="in-form">
                <StepShell v-model:step="step" :form="form" :steps="content.steps" submit-label="Submit nomination" @next="next" @submit="submit">
                    <div v-show="step === 0" class="grid gap-6">
                        <RadioCards v-model="form.category" label="Award category" name="category" :options="content.categories" required :error="form.errors.category" :help="content.provisional ? 'Categories are provisional until the award guidelines are published.' : undefined" />
                        <RadioCards v-model="form.self_nominated" label="Who are you nominating?" name="self_nominated" :options="who" required :error="form.errors.self_nominated" />
                    </div>
                    <div v-show="step === 1" class="grid gap-6">
                        <fieldset v-if="!self" class="grid gap-5 sm:grid-cols-2">
                            <legend class="in-group">Your details</legend>
                            <TextField v-model="form.nominator_name" label="Your name" name="nominator_name" required :error="form.errors.nominator_name" class="sm:col-span-2" />
                            <TextField v-model="form.nominator_email" label="Your email" name="nominator_email" type="email" required :error="form.errors.nominator_email" />
                            <TextField v-model="form.nominator_mobile" label="Your mobile number" name="nominator_mobile" type="mobile" required :error="form.errors.nominator_mobile" />
                        </fieldset>
                        <fieldset class="grid gap-5 sm:grid-cols-2">
                            <legend class="in-group">{{ self ? 'Your details' : 'Nominee details' }}</legend>
                            <TextField v-model="form.nominee_name" :label="self ? 'Full name' : 'Nominee name'" name="nominee_name" required :error="form.errors.nominee_name" class="sm:col-span-2" />
                            <TextField v-model="form.nominee_email" :label="self ? 'Email' : 'Nominee email'" name="nominee_email" type="email" required :error="form.errors.nominee_email" />
                            <TextField v-model="form.nominee_mobile" :label="self ? 'Mobile number' : 'Nominee mobile number'" name="nominee_mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.nominee_mobile" />
                            <SelectField v-model="form.district" label="District" name="district" :groups="content.districts" required :error="form.errors.district" class="sm:col-span-2" />
                        </fieldset>
                    </div>
                    <div v-show="step === 2" class="grid gap-6">
                        <TextareaField v-model="form.achievements" label="Achievements" name="achievements" :max="2000" :rows="6" required help="Clients served, results delivered, growth, awards, and any community work." :error="form.errors.achievements" />
                        <LinksField v-model="form.portfolio" label="Portfolio links" name="portfolio" :errors="form.errors as Record<string, string>" />
                        <LinksField v-model="form.profiles" label="Marketplace profiles" name="profiles" :platforms="content.platforms" :errors="form.errors as Record<string, string>" />
                        <FileField v-model="form.files" label="Supporting documents" name="files" :help="t('inner.form.fileHelp', { mb: 5, max: 5 })" :error="form.errors.files" />
                    </div>
                    <div v-show="step === 3" class="grid gap-6">
                        <dl class="grid gap-3 rounded-2xl bg-mist/60 p-5 sm:grid-cols-2">
                            <div><dt class="in-meta">Category</dt><dd class="font-bold">{{ category ?? '-' }}</dd></div>
                            <div><dt class="in-meta">Nominee</dt><dd class="font-bold">{{ form.nominee_name || '-' }}</dd></div>
                            <div><dt class="in-meta">Nominated by</dt><dd class="font-bold">{{ self ? 'Self' : form.nominator_name || '-' }}</dd></div>
                            <div><dt class="in-meta">Documents</dt><dd class="font-bold">{{ form.files.length }}</dd></div>
                        </dl>
                        <label class="flex cursor-pointer gap-3 rounded-xl border bg-snow p-4" :class="form.errors.declaration ? 'border-red' : 'border-night/15'" data-field="declaration">
                            <input v-model="form.declaration" type="checkbox" name="declaration" class="mt-1 size-5 shrink-0 accent-[var(--green)]" />
                            <span>{{ content.declaration }} <span class="text-red-ink" aria-hidden="true">*</span></span>
                        </label>
                        <p v-if="form.errors.declaration" class="-mt-4 text-sm font-medium text-red-ink">{{ form.errors.declaration }}</p>
                        <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
                    </div>
                </StepShell>
            </div>
            <template #aside>
                <p class="mt-5 rounded-xl bg-white/70 p-4 text-sm text-ink-2">{{ content.eligibility }}</p>
            </template>
        </FormLayout>
    </div>
</template>
