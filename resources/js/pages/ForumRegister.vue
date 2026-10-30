<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ChipsField from '@/components/forms/ChipsField.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
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

// Brief §12: the forum's own application (FRM reference): profile, skills, marketplace profiles, consent. No NID unless required.
const props = defineProps<{
    page: InnerPage;
    content: { districts: OptionGroup[]; experience: Option[]; availability: Option[]; skills: Option[]; platforms: Option[]; consent: Consent; steps: string[]; eligibility: string; aside: string[] };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const step = ref(0);
const form = useForm({ name: '', email: '', mobile: '', district: '', experience_level: '', skills: [] as string[], services: '', availability: '', profiles: [{ platform: '', url: '' }] as LinkRow[], consent: false, hp_company: '' });
const draft = useFormDraft('forum', form, ['consent', 'hp_company']);
const { reference, reveal } = useSubmitted('forum');

// Step 1 is checked here so people are not sent back from the server for a missing name.
function next() {
    form.clearErrors();
    const need: Record<string, string> = { name: 'Enter your full name.', email: 'Enter your email.', mobile: 'Enter your mobile number.', district: 'Choose your district.' };
    Object.entries(need).forEach(([k, msg]) => !String(form[k as keyof typeof form] ?? '').trim() && form.setError(k as 'name', msg));
    if (!Object.keys(form.errors).length) step.value = 1;
}
const submit = () =>
    form.post('/forum/register', {
        preserveScroll: true,
        onSuccess: () => (draft.clear(), form.reset(), (step.value = 0), reveal()),
        onError: (e) => (step.value = ['name', 'email', 'mobile', 'district'].some((k) => k in e) ? 0 : 1),
    });
void props;
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <FormLayout :points="content.aside" icon="id" tone="tone-blue" title="Before you apply">
            <FormSuccess v-if="reference" title="Application received" :reference="reference" reference-label="Your reference number" body="BACCO reviews applications once the membership rules are final and will email you about your status.">
                <InBtn href="/forum">About the forum</InBtn>
                <InBtn href="/register" tone="ghost">Register for the summit</InBtn>
            </FormSuccess>
            <div v-else class="in-form">
                <StepShell v-model:step="step" :form="form" :steps="content.steps" submit-label="Submit application" @next="next" @submit="submit">
                    <div v-show="step === 0" class="grid gap-5 sm:grid-cols-2">
                        <TextField v-model="form.name" label="Full name" name="name" required autocomplete="name" :error="form.errors.name" class="sm:col-span-2" />
                        <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                        <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                        <SelectField v-model="form.district" label="District" name="district" :groups="content.districts" required :error="form.errors.district" class="sm:col-span-2" />
                    </div>
                    <div v-show="step === 1" class="grid gap-6">
                        <RadioCards v-model="form.experience_level" label="Freelancing experience" name="experience_level" :options="content.experience" required :error="form.errors.experience_level" columns="sm:grid-cols-2" />
                        <ChipsField v-model="form.skills" label="Skills" name="skills" :options="content.skills" required :error="form.errors.skills" />
                        <TextareaField v-model="form.services" label="Services you offer" name="services" :max="1000" :rows="3" :error="form.errors.services" />
                        <SelectField v-model="form.availability" label="Availability" name="availability" :options="content.availability" :error="form.errors.availability" />
                        <LinksField v-model="form.profiles" label="Marketplace profiles" name="profiles" :platforms="content.platforms" :errors="form.errors as Record<string, string>" help="Upwork, Fiverr, LinkedIn or any profile clients use to find you." />
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
