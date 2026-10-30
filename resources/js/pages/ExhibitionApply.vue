<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
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
import type { Consent, InnerPage, Option } from '@/types/inner';

// Brief §11 booth application (EXH reference): organisation, sector, booth, contact, products, branding needs.
defineProps<{ page: InnerPage; content: { sectors: Option[]; booths: Option[]; consent: Consent; steps: string[]; aside: string[] } }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const step = ref(0);
const form = useForm({ organisation: '', sector: '', contact_person: '', designation: '', email: '', mobile: '', booth_requirement: '', products: '', branding_needs: '', requirements: '', consent: false, hp_company: '' });
const draft = useFormDraft('exhibit', form, ['consent', 'hp_company']);
const { reference, reveal } = useSubmitted('exhibit');
const groups: Record<number, Record<string, string>> = {
    0: { organisation: 'Enter your organisation.', sector: 'Choose a sector.' },
    1: { contact_person: 'Enter a contact person.', email: 'Enter an email.', mobile: 'Enter a mobile number.' },
};
function next() {
    form.clearErrors();
    Object.entries(groups[step.value] ?? {}).forEach(([k, msg]) => !String(form[k as keyof typeof form] ?? '').trim() && form.setError(k as 'organisation', msg));
    if (!Object.keys(form.errors).length) step.value++;
}
const submit = () =>
    form.post('/exhibition/apply', {
        preserveScroll: true,
        onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
        onError: (e) => (step.value = Object.keys(groups).map(Number).find((n) => Object.keys(groups[n]).some((k) => k in e)) ?? 2),
    });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <FormLayout :points="content.aside" icon="store" tone="tone-violet">
            <FormSuccess v-if="reference" title="Application received" :reference="reference" reference-label="Your reference number" body="We will confirm your booth and send the next steps by email once the floor plan is final.">
                <InBtn href="/exhibition">Back to the exhibition</InBtn>
                <InBtn href="/sponsorship-opportunity" tone="ghost">Sponsorship options</InBtn>
            </FormSuccess>
            <div v-else class="in-form">
                <StepShell v-model:step="step" :form="form" :steps="content.steps" submit-label="Submit application" @next="next" @submit="submit">
                    <div v-show="step === 0" class="grid gap-5">
                        <TextField v-model="form.organisation" label="Organisation" name="organisation" required autocomplete="organization" :error="form.errors.organisation" />
                        <SelectField v-model="form.sector" label="Sector" name="sector" :options="content.sectors" required :error="form.errors.sector" />
                    </div>
                    <div v-show="step === 1" class="grid gap-5 sm:grid-cols-2">
                        <TextField v-model="form.contact_person" label="Contact person" name="contact_person" required autocomplete="name" :error="form.errors.contact_person" />
                        <TextField v-model="form.designation" label="Designation" name="designation" :error="form.errors.designation" />
                        <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                        <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                    </div>
                    <div v-show="step === 2" class="grid gap-6">
                        <RadioCards v-model="form.booth_requirement" label="Booth requirement" name="booth_requirement" :options="content.booths" :error="form.errors.booth_requirement" columns="sm:grid-cols-2" />
                        <TextareaField v-model="form.products" label="Products or services you will show" name="products" :max="2000" :rows="4" required :error="form.errors.products" />
                        <TextareaField v-model="form.branding_needs" label="Branding needs" name="branding_needs" :max="2000" :rows="3" :error="form.errors.branding_needs" />
                        <TextareaField v-model="form.requirements" label="Other requirements" name="requirements" :max="2000" :rows="3" help="Power, internet, furniture, screens or anything else." :error="form.errors.requirements" />
                        <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
                    </div>
                </StepShell>
            </div>
        </FormLayout>
    </div>
</template>
