<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ErrorSummary from '@/components/forms/ErrorSummary.vue';
import Field from '@/components/forms/Field.vue';
import FormPage from '@/components/forms/FormPage.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import PillRadios from '@/components/forms/PillRadios.vue';
import { iconInputClass } from '@/components/forms/input';
import { useFormDraft } from '@/components/forms/useFormDraft';
import Glyph from '@/components/inner/Glyph.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import { useShared } from '@/composables/useShared';
import type { Consent, InnerPage, Option, OptionGroup } from '@/types/inner';

// Brief §8 summit registration, laid out like the client mock-up (FormPage): centred title block with the event facts, then one
// white card holding every field (icon inputs, attendee-type radio cards, interest checkboxes, consent, gold submit).
// Record (REG reference), confirmation email and attendee database are unchanged (SubmissionController::register).
const props = defineProps<{
    page: InnerPage;
    content: { districts: OptionGroup[]; attendeeTypes: Option[]; interests: Option[]; consent: Consent };
}>();
const { t } = useI18n();
const { summit } = useShared();

const form = useForm({ name: '', email: '', mobile: '', organisation: '', designation: '', district: '', attendee_type: '', interests: [] as string[], consent: false, hp_company: '' });
const draft = useFormDraft('register', form, ['consent', 'hp_company']);
const { reference, reveal } = useSubmitted('register');
const submit = () => form.post('/register', { preserveScroll: true, onSuccess: () => (draft.clear(), form.reset(), reveal()) });

// Date and venue stay "to be confirmed" until config/summit.php says otherwise.
const facts = computed(() => [
    { icon: 'calendar' as const, text: summit.value.dateConfirmed ? new Date(summit.value.startsAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.lateNov', { edition: summit.value.edition }) },
    { icon: 'pin' as const, text: summit.value.venue ? `${summit.value.venue}, ${summit.value.city}` : `Venue TBC, ${summit.value.city}` },
    { icon: 'people' as const, text: 'In-Person Event' },
]);

const fields: { key: 'name' | 'email' | 'mobile' | 'organisation' | 'designation'; label: string; icon: GlyphName; placeholder: string; type?: string; autocomplete: string }[] = [
    { key: 'name', label: 'Full Name', icon: 'user', placeholder: 'Enter your full name', autocomplete: 'name' },
    { key: 'email', label: 'Email Address', icon: 'mail', placeholder: 'Enter your email address', type: 'email', autocomplete: 'email' },
    { key: 'mobile', label: 'Mobile Number', icon: 'phone', placeholder: '01XXXXXXXXX', type: 'tel', autocomplete: 'tel' },
    { key: 'organisation', label: 'Organization / Institution', icon: 'building', placeholder: 'Your organization or institution', autocomplete: 'organization' },
    { key: 'designation', label: 'Designation', icon: 'briefcase', placeholder: 'Your designation', autocomplete: 'organization-title' },
];
const input = iconInputClass;

// The versioned consent wording, with its "Privacy Policy" mention linked.
const consentParts = computed(() => props.content.consent.text.split('Privacy Policy'));
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <FormPage eyebrow="Summit Registration" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" :points="facts" center label="Registration form">
        <FormSuccess v-if="reference" title="You're registered" :reference="reference" reference-label="Your reference number" body="We have emailed your confirmation. Your e-ticket with a QR code follows once the date and venue are confirmed.">
            <InBtn href="/agenda">Explore the agenda</InBtn>
            <InBtn href="/" tone="ghost">Back to home</InBtn>
        </FormSuccess>

        <form v-else novalidate @submit.prevent="submit">
            <h2 class="text-2xl font-bold text-night md:text-[1.75rem]">Your Registration Details</h2>
            <p class="mt-1 text-ink-2 md:text-lg">Please provide the following information to complete your summit registration.</p>

            <ErrorSummary :errors="form.errors" class="mt-6" />
            <!-- Honeypot: humans never see or fill it (App\Http\Middleware\Honeypot). -->
            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label>{{ t('inner.form.honeypot') }} <input v-model="form.hp_company" type="text" name="hp_company" tabindex="-1" autocomplete="off" /></label>
            </div>

            <div class="mt-8 grid gap-x-9 gap-y-6 sm:grid-cols-2">
                <Field v-for="f in fields" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                    <div class="relative">
                        <Glyph :name="f.icon" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <input
                            :id="id"
                            v-model.trim="form[f.key]"
                            :type="f.type ?? 'text'"
                            :name="f.key"
                            :inputmode="f.key === 'mobile' ? 'tel' : undefined"
                            :autocomplete="f.autocomplete"
                            :placeholder="f.placeholder"
                            required
                            :aria-invalid="invalid"
                            :aria-describedby="describedby"
                            :class="input(invalid)"
                        />
                    </div>
                </Field>
                <Field v-slot="{ id, describedby, invalid }" label="City" name="district" required :error="form.errors.district">
                    <div class="relative">
                        <Glyph name="pin" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-2" />
                        <select :id="id" v-model="form.district" name="district" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[input(invalid), 'appearance-none pr-11', !form.district && 'text-ink-2/80']">
                            <option value="">Select your city</option>
                            <optgroup v-for="g in content.districts" :key="g.label" :label="g.label">
                                <option v-for="o in g.options" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                            </optgroup>
                        </select>
                        <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                    </div>
                </Field>
            </div>

            <PillRadios v-model="form.attendee_type" label="Attendee Type" name="attendee_type" :options="content.attendeeTypes" required :error="form.errors.attendee_type" class="mt-7" />

            <fieldset data-field="interests" class="mt-7">
                <legend class="text-base font-medium text-night">Areas of Interest <span class="text-red-ink" aria-hidden="true">*</span> <span class="text-sm text-ink-2">(Select all that apply)</span></legend>
                <div class="mt-3 flex flex-wrap gap-3">
                    <label
                        v-for="o in content.interests"
                        :key="o.value"
                        class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border px-4 text-[0.9375rem] text-night transition-colors duration-200 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-green/25"
                        :class="form.interests.includes(o.value) ? 'border-green/50 bg-[#dff3f3]' : 'border-night/15 bg-white hover:border-night/30'"
                    >
                        <input v-model="form.interests" type="checkbox" name="interests[]" :value="o.value" class="size-[1.125rem] shrink-0 rounded accent-[var(--green)]" />
                        {{ o.label }}
                    </label>
                </div>
                <p v-if="form.errors.interests" class="mt-1.5 text-sm font-medium text-red-ink">{{ form.errors.interests }}</p>
            </fieldset>

            <div data-field="consent" class="mt-8">
                <label class="flex cursor-pointer items-start gap-3 text-night">
                    <input v-model="form.consent" type="checkbox" name="consent" required :aria-invalid="!!form.errors.consent" class="mt-0.5 size-6 shrink-0 accent-[var(--green)]" />
                    <span>
                        <template v-for="(part, i) in consentParts" :key="i"><Link v-if="i" href="/privacy-policy" class="text-green underline underline-offset-2">Privacy Policy</Link>{{ part }}</template>
                        <span class="text-red-ink" aria-hidden="true"> *</span>
                    </span>
                </label>
                <p v-if="form.errors.consent" class="mt-1.5 text-sm font-medium text-red-ink">{{ form.errors.consent }}</p>
            </div>

            <button
                type="submit"
                class="mx-auto mt-9 flex min-h-14 w-full max-w-[47rem] items-center justify-center gap-3 rounded-xl bg-gradient-to-b from-[#f2bd3e] to-[#e3a92e] px-6 text-lg font-bold text-night shadow-[0_14px_30px_-16px_rgb(180_120_20/0.7)] transition-[filter,transform] duration-300 hover:brightness-105 active:translate-y-px disabled:opacity-70 md:text-xl"
                :disabled="form.processing"
            >
                {{ form.processing ? t('inner.form.sending') : 'Register for the Summit' }}
                <svg width="18" height="18" viewBox="0 0 14 14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11 11 3M5 3h6v6" /></svg>
            </button>
        </form>
    </FormPage>
</template>
