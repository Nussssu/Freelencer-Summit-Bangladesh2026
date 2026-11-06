<script setup lang="ts">
import { usePlaceholder } from '@/composables/usePlaceholder';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BdPrefix from '@/components/forms/BdPrefix.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import ErrorSummary from '@/components/forms/ErrorSummary.vue';
import Field from '@/components/forms/Field.vue';
import FormNav from '@/components/forms/FormNav.vue';
import FormPage from '@/components/forms/FormPage.vue';
import FormSteps from '@/components/forms/FormSteps.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import { iconInputClass } from '@/components/forms/input';
import RadioCards from '@/components/forms/RadioCards.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import { useFormDraft } from '@/components/forms/useFormDraft';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import type { Consent, InnerPage, Option } from '@/types/inner';

// Brief §11 booth application (EXH reference), laid out like the client mock-up (FormPage): organisation and contact,
// then booth, products and needs, then a review with consent. Each step is validated here first; the server validates all.
const props = defineProps<{ page: InnerPage; content: { sectors: Option[]; booths: Option[]; consent: Consent; steps: string[] } }>();
const { t } = useI18n();
const ph = usePlaceholder();

const step = ref(0);
const form = useForm({ organisation: '', sector: '', contact_person: '', designation: '', email: '', mobile: '', booth_requirement: '', products: '', branding_needs: '', requirements: '', consent: false, hp_company: '' });
const draft = useFormDraft('exhibit', form, ['consent', 'hp_company']);
const { reference, reveal } = useSubmitted('exhibit');

const points: { icon: GlyphName; text: string }[] = [
    { icon: 'store', text: 'Showcase Your Brand' },
    { icon: 'people', text: 'Connect with Talent & Business' },
    { icon: 'chart', text: 'Generate New Opportunities' },
];
const heads = ['Provide your organization information for exhibition registration.', 'Tell us the space you need and what you will show.', 'Check the details, then confirm and submit.'];

type TextKey = 'organisation' | 'contact_person' | 'designation' | 'email' | 'mobile';
const contact: { key: TextKey; label: string; icon: GlyphName; type?: string; autocomplete: string }[] = [
    { key: 'contact_person', label: 'Contact Person', icon: 'user', autocomplete: 'name' },
    { key: 'designation', label: 'Designation', icon: 'briefcase', autocomplete: 'organization-title' },
    { key: 'email', label: 'Email Address', icon: 'mail', type: 'email', autocomplete: 'email' },
    { key: 'mobile', label: 'Mobile Number', icon: 'phone', type: 'tel', autocomplete: 'tel' },
];

const groups: Record<number, Record<string, string>> = {
    0: { organisation: 'Enter your organization or company name.', sector: 'Choose your industry or sector.', contact_person: 'Enter a contact person.', designation: 'Enter a designation.', email: 'Enter an email address.', mobile: 'Enter a mobile number.' },
    1: { products: 'Describe the products or services you will show.' },
};
function next() {
    form.clearErrors();
    Object.entries(groups[step.value] ?? {}).forEach(([k, msg]) => !String(form[k as keyof typeof form] ?? '').trim() && form.setError(k as 'organisation', msg));
    if (!Object.keys(form.errors).length) step.value++;
}
const last = computed(() => step.value === props.content.steps.length - 1);
const submit = () =>
    form.post('/exhibition/apply', {
        preserveScroll: true,
        onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
        onError: (e) => (step.value = Object.keys(groups).map(Number).find((n) => Object.keys(groups[n]).some((k) => k in e)) ?? 1),
    });

const saved = ref('');
const saveDraft = () => (saved.value = draft.save() ? 'Draft saved on this device.' : 'Your browser blocked saving a draft.');
const label = (options: Option[], value: string) => options.find((o) => o.value === value)?.label ?? '-';
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <FormPage eyebrow="Exhibition" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" :points="points" center label="Exhibition registration form">
        <FormSuccess v-if="reference" title="Application received" :reference="reference" reference-label="Your reference number" body="We will confirm your booth and send the next steps by email once the floor plan is final.">
            <InBtn href="/exhibition">Back to the exhibition</InBtn>
            <InBtn href="/sponsorship-opportunity" tone="ghost">Sponsorship options</InBtn>
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

            <!-- 1. Organization details -->
            <div v-show="step === 0" class="mt-7 grid gap-x-9 gap-y-6 border-b border-line-light pb-9 sm:grid-cols-2">
                <Field v-slot="{ id, describedby, invalid }" label="Organization / Company Name" name="organisation" required :error="form.errors.organisation">
                    <div class="relative">
                        <input :id="id" v-model.trim="form.organisation" type="text" name="organisation" :placeholder="ph('organisation')" autocomplete="organization" required :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" label="Industry / Sector" name="sector" required :error="form.errors.sector">
                    <div class="relative">
                        <select :id="id" v-model="form.sector" name="sector" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.sector && 'text-ink-2/80']">
                            <option value="">{{ ph('sector') }}</option>
                            <option v-for="o in content.sectors" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                        </select>
                        <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                    </div>
                </Field>
                <Field v-for="f in contact" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                    <div class="relative">
                        <BdPrefix v-if="f.type === 'tel'" />
                        <input :id="id" v-model.trim="form[f.key]" :type="f.type ?? 'text'" :name="f.key" :placeholder="ph(f.key)" :inputmode="f.type === 'tel' ? 'tel' : undefined" :autocomplete="f.autocomplete" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), f.type === 'tel' && 'pl-[6.75rem]!']" />
                    </div>
                </Field>
            </div>

            <!-- 2. Booth and requirements -->
            <div v-show="step === 1" class="mt-7 grid gap-7">
                <RadioCards v-model="form.booth_requirement" label="Booth requirement" name="booth_requirement" :options="content.booths" :error="form.errors.booth_requirement" columns="sm:grid-cols-2" />
                <TextareaField v-model="form.products" label="Products or services you will show" name="products" :max="2000" :rows="4" required :error="form.errors.products" />
                <TextareaField v-model="form.branding_needs" label="Branding needs" name="branding_needs" :max="2000" :rows="3" :error="form.errors.branding_needs" />
                <TextareaField v-model="form.requirements" label="Other requirements" name="requirements" :max="2000" :rows="3" help="Power, internet, furniture, screens or anything else." :error="form.errors.requirements" />
            </div>

            <!-- 3. Review and submit -->
            <div v-show="step === 2" class="mt-7 grid gap-6">
                <dl class="grid gap-4 rounded-2xl bg-[#eef7f8] p-5 sm:grid-cols-2 md:p-6">
                    <div><dt class="text-sm text-ink-2">Organization</dt><dd class="font-bold">{{ form.organisation || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Industry / sector</dt><dd class="font-bold">{{ label(content.sectors, form.sector) }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Contact person</dt><dd class="font-bold">{{ [form.contact_person, form.designation].filter(Boolean).join(', ') || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Email and mobile</dt><dd class="font-bold break-words">{{ [form.email, form.mobile].filter(Boolean).join(' · ') || '-' }}</dd></div>
                    <div><dt class="text-sm text-ink-2">Booth</dt><dd class="font-bold">{{ label(content.booths, form.booth_requirement) }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-ink-2">Products or services</dt><dd class="font-bold whitespace-pre-line">{{ form.products || '-' }}</dd></div>
                </dl>
                <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
            </div>

            <FormNav :back="step > 0" :label="last ? 'Submit Application' : `Next: ${content.steps[step + 1]}`" :processing="form.processing" :saved="saved" @back="step--" @save="saveDraft" />
        </form>
    </FormPage>
</template>
