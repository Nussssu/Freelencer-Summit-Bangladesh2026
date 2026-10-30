<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ChipsField from '@/components/forms/ChipsField.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import SelectField from '@/components/forms/SelectField.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextField from '@/components/forms/TextField.vue';
import { useFormDraft } from '@/components/forms/useFormDraft';
import FormLayout from '@/components/inner/FormLayout.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import TbcNotice from '@/components/inner/TbcNotice.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, Option, OptionGroup } from '@/types/inner';

// Brief §8 summit registration: its own fields, record (REG reference), confirmation email and attendee database.
defineProps<{
    page: InnerPage;
    content: { districts: OptionGroup[]; attendeeTypes: Option[]; interests: Option[]; consent: Consent; aside: { points: string[]; others: { label: string; href: string }[] } };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const form = useForm({ name: '', email: '', mobile: '', organisation: '', designation: '', district: '', attendee_type: '', interests: [] as string[], consent: false, hp_company: '' });
const draft = useFormDraft('register', form, ['consent', 'hp_company']);
const { reference, reveal } = useSubmitted('register');
const submit = () => form.post('/register', { preserveScroll: true, onSuccess: () => (draft.clear(), form.reset(), reveal()) });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" variant="facts" />

    <div ref="root">
        <FormLayout :points="content.aside.points" icon="ticket">
            <FormSuccess v-if="reference" title="You're registered" :reference="reference" reference-label="Your reference number" body="We have emailed your confirmation. Your e-ticket with a QR code follows once the date and venue are confirmed.">
                <InBtn href="/agenda">Explore the agenda</InBtn>
                <InBtn href="/" tone="ghost">Back to home</InBtn>
            </FormSuccess>
            <div v-else class="in-form">
                <StepShell :form="form" submit-label="Register" @submit="submit">
                    <fieldset>
                        <legend class="in-group">About you</legend>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <TextField v-model="form.name" label="Full name" name="name" required autocomplete="name" :error="form.errors.name" class="sm:col-span-2" />
                            <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                            <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                            <TextField v-model="form.organisation" label="Organisation or institution" name="organisation" autocomplete="organization" :error="form.errors.organisation" />
                            <TextField v-model="form.designation" label="Designation" name="designation" autocomplete="organization-title" :error="form.errors.designation" />
                            <SelectField v-model="form.district" label="City or district" name="district" :groups="content.districts" required :error="form.errors.district" class="sm:col-span-2" />
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="in-group">Your visit</legend>
                        <div class="grid gap-5">
                            <SelectField v-model="form.attendee_type" label="I am attending as" name="attendee_type" :options="content.attendeeTypes" required :error="form.errors.attendee_type" />
                            <ChipsField v-model="form.interests" label="Interests" name="interests" :options="content.interests" help="Choose any that apply. We use this to plan sessions." :error="form.errors.interests" />
                            <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" />
                        </div>
                    </fieldset>
                </StepShell>
            </div>
            <template #aside>
                <TbcNotice class="mt-6" />
                <h3 class="mt-6 font-bold text-night">Looking for something else?</h3>
                <ul class="mt-2 space-y-1">
                    <li v-for="o in content.aside.others" :key="o.href"><Link :href="o.href" class="in-link min-h-10!">{{ o.label }} <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg></Link></li>
                </ul>
            </template>
        </FormLayout>
    </div>
</template>
