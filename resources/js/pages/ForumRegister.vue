<script setup lang="ts">
import { usePlaceholder } from '@/composables/usePlaceholder';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BdPrefix from '@/components/forms/BdPrefix.vue';
import ChipsField from '@/components/forms/ChipsField.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import ErrorSummary from '@/components/forms/ErrorSummary.vue';
import Field from '@/components/forms/Field.vue';
import FormNav from '@/components/forms/FormNav.vue';
import FormPage from '@/components/forms/FormPage.vue';
import FormSteps from '@/components/forms/FormSteps.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import { iconInputClass } from '@/components/forms/input';
import LinksField from '@/components/forms/LinksField.vue';
import PillRadios from '@/components/forms/PillRadios.vue';
import RadioCards from '@/components/forms/RadioCards.vue';
import SelectField from '@/components/forms/SelectField.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import { useFormDraft } from '@/components/forms/useFormDraft';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import type { Consent, InnerPage, LinkRow, Option, OptionGroup } from '@/types/inner';

// Brief §12: the forum's own application (FRM reference), laid out like the client mock-up (FormPage): personal profile,
// freelancing details, then a review with consent. NID stays optional (and encrypted) unless BACCO makes it mandatory.
const props = defineProps<{
    page: InnerPage;
    content: { districts: OptionGroup[]; experience: Option[]; availability: Option[]; skills: Option[]; platforms: Option[]; nationalities: Option[]; profileTypes: Option[]; consent: Consent; steps: string[] };
}>();
const { t } = useI18n();
const ph = usePlaceholder();

const step = ref(0);
const form = useForm({
    name: '', mobile: '', email: '', nationality: 'bangladeshi', district: '', nid: '', profile_type: '',
    experience_level: '', skills: [] as string[], services: '', availability: '', profiles: [{ platform: '', url: '' }] as LinkRow[],
    consent: false, hp_company: '',
});
// NID is never kept in browser storage.
const draft = useFormDraft('forum', form, ['nid', 'consent', 'hp_company']);
const { reference, reveal } = useSubmitted('forum');

const points: { icon: GlyphName; text: string }[] = [
    { icon: 'people', text: 'Join a National Community' },
    { icon: 'layers', text: 'Access Resources & Programs' },
    { icon: 'rocket', text: 'Grow Your Freelancing Career' },
];
const heads = ['Provide your basic information to create your forum profile.', 'Tell us about your experience, skills and where clients find you.', 'Check the details, then confirm and submit.'];

type TextKey = 'name' | 'mobile' | 'email';
const text: { key: TextKey; label: string; icon: GlyphName; type?: string; autocomplete: string }[] = [
    { key: 'name', label: 'Full Name', icon: 'user', autocomplete: 'name' },
    { key: 'mobile', label: 'Mobile Number', icon: 'phone', type: 'tel', autocomplete: 'tel' },
    { key: 'email', label: 'Email Address', icon: 'mail', type: 'email', autocomplete: 'email' },
];

const groups: Record<number, Record<string, string>> = {
    0: { name: 'Enter your full name.', mobile: 'Enter your mobile number.', email: 'Enter your email address.', nationality: 'Choose your nationality.', district: 'Choose your city.', profile_type: 'Choose a profile type.' },
    1: { experience_level: 'Choose your freelancing experience.' },
};
function next() {
    form.clearErrors();
    Object.entries(groups[step.value] ?? {}).forEach(([k, msg]) => !String(form[k as keyof typeof form] ?? '').trim() && form.setError(k as 'name', msg));
    if (step.value === 1 && !form.skills.length) form.setError('skills', 'Choose at least one skill.');
    if (!Object.keys(form.errors).length) step.value++;
}
const last = computed(() => step.value === props.content.steps.length - 1);
const fieldStep = (k: string) => (k in groups[0] || k === 'nid' ? 0 : k === 'consent' ? 2 : 1);
const submit = () =>
    form.post('/forum/register', {
        preserveScroll: true,
        onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
        onError: (e) => (step.value = Math.min(...Object.keys(e).map(fieldStep))),
    });

const saved = ref('');
const saveDraft = () => (saved.value = draft.save() ? 'Draft saved on this device. Your NID number is not saved.' : 'Your browser blocked saving a draft.');
const label = (options: Option[], value: string) => options.find((o) => o.value === value)?.label ?? '-';
const district = computed(() => props.content.districts.flatMap((g) => g.options).find((o) => o.value === form.district)?.label ?? '-');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <FormPage eyebrow="BACCO Freelancer Forum" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" :points="points" center label="Forum registration form">
        <FormSuccess v-if="reference" title="Application received" :reference="reference" reference-label="Your reference number" body="BACCO reviews applications once the membership rules are final and will email you about your status.">
            <InBtn href="/forum">About the forum</InBtn>
            <InBtn href="/register" tone="ghost">Register for the summit</InBtn>
        </FormSuccess>

        <form v-else novalidate @submit.prevent="last ? submit() : next()">
            <FormSteps v-model="step" :steps="content.steps" />

            <h2 class="mt-8 text-2xl font-bold text-night md:text-[1.75rem]">{{ step + 1 }}. {{ content.steps[step] }}</h2>
            <p class="mt-1 text-ink-2 md:text-lg">{{ heads[step] }}</p>

            <ErrorSummary :errors="form.errors" class="mt-6" />
            <!-- Honeypot: humans never see or fill it (App\Http\Middleware\Honeypot). -->
            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label>{{ t('inner.form.honeypot') }} <input v-model="form.hp_company" type="text" name="hp_company" tabindex="-1" autocomplete="off" /></label>
            </div>

            <!-- 1. Personal profile -->
            <div v-show="step === 0" class="mt-7 border-b border-line-light pb-9">
                <div class="grid gap-x-9 gap-y-6 sm:grid-cols-2">
                    <Field v-for="f in text.slice(0, 2)" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                        <div class="relative">
                            <BdPrefix v-if="f.type === 'tel'" />
                            <input :id="id" v-model.trim="form[f.key]" :type="f.type ?? 'text'" :name="f.key" :placeholder="ph(f.key)" :inputmode="f.type === 'tel' ? 'tel' : undefined" :autocomplete="f.autocomplete" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), f.type === 'tel' && 'pl-[6.75rem]!']" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" :label="text[2].label" name="email" required :error="form.errors.email">
                        <div class="relative">
                            <input :id="id" v-model.trim="form.email" type="email" name="email" :placeholder="ph('email')" autocomplete="email" required :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Nationality" name="nationality" required :error="form.errors.nationality">
                        <div class="relative">
                            <select :id="id" v-model="form.nationality" name="nationality" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11']">
                                <option v-for="o in content.nationalities" :key="o.value" :value="o.value">{{ o.label }}</option>
                            </select>
                            <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Current City" name="district" required :error="form.errors.district">
                        <div class="relative">
                            <select :id="id" v-model="form.district" name="district" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.district && 'text-ink-2/80']">
                                <option value="">{{ ph('district') }}</option>
                                <optgroup v-for="g in content.districts" :key="g.label" :label="g.label">
                                    <option v-for="o in g.options" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                                </optgroup>
                            </select>
                            <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="NID Number" name="nid" :error="form.errors.nid">
                        <div class="relative">
                            <input :id="id" v-model.trim="form.nid" type="text" name="nid" :placeholder="ph('nid')" inputmode="numeric" autocomplete="off" maxlength="17" :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                        </div>
                    </Field>
                </div>
                <PillRadios v-model="form.profile_type" label="Profile Type" name="profile_type" :options="content.profileTypes" required :error="form.errors.profile_type" columns="lg:grid-cols-4" class="mt-7" />
            </div>

            <!-- 2. Freelancing details -->
            <div v-show="step === 1" class="mt-7 grid gap-7">
                <RadioCards v-model="form.experience_level" label="Freelancing experience" name="experience_level" :options="content.experience" required :error="form.errors.experience_level" columns="sm:grid-cols-2" />
                <ChipsField v-model="form.skills" label="Skills" name="skills" :options="content.skills" required :error="form.errors.skills" />
                <TextareaField v-model="form.services" label="Services you offer" name="services" :max="1000" :rows="3" :error="form.errors.services" />
                <SelectField v-model="form.availability" label="Availability" name="availability" :options="content.availability" :error="form.errors.availability" />
                <LinksField v-model="form.profiles" label="Marketplace profiles" name="profiles" :platforms="content.platforms" :errors="form.errors as Record<string, string>" help="Upwork, Fiverr, LinkedIn or any profile clients use to find you." />
            </div>

            <!-- 3. Review and submit -->
            <div v-show="step === 2" class="mt-7 grid gap-6">
                <dl class="grid gap-4 rounded-2xl bg-[#eef7f8] p-5 sm:grid-cols-2 md:p-6">
                    <div><dt class="text-sm text-ink-2">Full name</dt><dd class="font-bold">{{ form.name || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Email and mobile</dt><dd class="font-bold break-words">{{ [form.email, form.mobile].filter(Boolean).join(' · ') || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">City</dt><dd class="font-bold">{{ district }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Profile type</dt><dd class="font-bold">{{ label(content.profileTypes, form.profile_type) }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Experience</dt><dd class="font-bold">{{ label(content.experience, form.experience_level) }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Skills</dt><dd class="font-bold">{{ form.skills.map((s) => label(content.skills, s)).join(', ') || '-' }}</dd></div>
                </dl>
                <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
            </div>

            <FormNav :back="step > 0" :label="last ? 'Submit Application' : `Next: ${content.steps[step + 1]}`" :processing="form.processing" :saved="saved" @back="step--" @save="saveDraft" />
        </form>
    </FormPage>
</template>
