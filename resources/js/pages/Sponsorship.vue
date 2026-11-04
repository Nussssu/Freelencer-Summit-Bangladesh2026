<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BdPrefix from '@/components/forms/BdPrefix.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import ErrorSummary from '@/components/forms/ErrorSummary.vue';
import Field from '@/components/forms/Field.vue';
import FormPage from '@/components/forms/FormPage.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import { iconInputClass } from '@/components/forms/input';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TierCard from '@/components/inner/TierCard.vue';
import TintCard from '@/components/inner/TintCard.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, DownloadItem, InnerPage, Option, SectionHeading, Tier } from '@/types/inner';

// Brief §10 sponsorship, laid out like the registration pages (FormPage): the stage-photo hero and the enquiry form
// (SPO reference, call-back option) first, then the tiers (provisional) with the prospectus download, then why sponsor.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'why' | 'tiers' | 'enquiry', SectionHeading>;
        why: { icon: GlyphName; tone: string; title: string; text: string }[];
        tiers: (Tier & { tone: string })[];
        tiersNote: string;
        prospectus: DownloadItem;
        form: { interests: Option[]; budgets: Option[]; consent: Consent };
        enquiryIntro: string;
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const form = useForm({ company: '', contact_person: '', designation: '', email: '', mobile: '', interest: '', budget: '', requirements: '', message: '', callback_requested: false, consent: false, hp_company: '' });
const { reference, reveal } = useSubmitted('sponsor', '#enquiry');
const submit = () => form.post('/sponsorship-opportunity', { preserveScroll: true, onSuccess: () => (form.reset(), reveal()) });

// The enquiry form follows the client mock-up: inputs in pairs, then the message.
const points: { icon: GlyphName; text: string }[] = [
    { icon: 'handshake', text: 'High-Visibility Brand Exposure' },
    { icon: 'people', text: 'Engage a Diverse Talent Community' },
    { icon: 'chart', text: 'Build Long-Term Impact' },
];
type TextKey = 'company' | 'contact_person' | 'designation' | 'mobile' | 'email';
const fields: { key: TextKey; label: string; icon: GlyphName; type?: string; autocomplete: string }[] = [
    { key: 'company', label: 'Company / Organization Name', icon: 'building', autocomplete: 'organization' },
    { key: 'contact_person', label: 'Contact Person', icon: 'user', autocomplete: 'name' },
    { key: 'designation', label: 'Designation', icon: 'briefcase', autocomplete: 'organization-title' },
    { key: 'mobile', label: 'Phone Number', icon: 'phone', type: 'tel', autocomplete: 'tel' },
    { key: 'email', label: 'Email Address', icon: 'mail', type: 'email', autocomplete: 'email' },
];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <div ref="root">
        <FormPage
            id="enquiry"
            class="scroll-mt-[calc(var(--header-h)+1rem)]"
            :title="content.headings.enquiry.title"
            :emphasis="content.headings.enquiry.emphasis"
            :points="points"
            center
            label="Sponsorship enquiry form"
        >
            <FormSuccess v-if="reference" title="Enquiry received" :reference="reference" reference-label="Your reference number" body="The partnerships team will contact you with the prospectus and next steps.">
                <InBtn href="/sponsors" tone="ghost">See current sponsors</InBtn>
            </FormSuccess>
            <form v-else novalidate @submit.prevent="submit">
                <h3 class="text-2xl font-bold text-night md:text-[1.75rem]">Send Your Sponsorship Enquiry</h3>
                <p class="mt-1 text-ink-2 md:text-lg">Share your interest and our partnership team will get back to you soon.</p>

                <ErrorSummary :errors="form.errors" class="mt-6" />
                <!-- Honeypot: humans never see or fill it (App\Http\Middleware\Honeypot). -->
                <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                    <label>{{ t('inner.form.honeypot') }} <input v-model="form.hp_company" type="text" name="hp_company" tabindex="-1" autocomplete="off" /></label>
                </div>

                <div class="mt-7 grid gap-x-9 gap-y-6 sm:grid-cols-2">
                    <Field v-for="f in fields" :key="f.key" v-slot="{ id, describedby, invalid }" :label="f.label" :name="f.key" required :error="form.errors[f.key]">
                        <div class="relative">
                            <BdPrefix v-if="f.type === 'tel'" />
                            <input :id="id" v-model.trim="form[f.key]" :type="f.type ?? 'text'" :name="f.key" :inputmode="f.type === 'tel' ? 'tel' : undefined" :autocomplete="f.autocomplete" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), f.type === 'tel' && 'pl-[6.75rem]!']" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Sponsorship Interest" name="interest" required :error="form.errors.interest">
                        <div class="relative">
                            <select :id="id" v-model="form.interest" name="interest" required :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.interest && 'text-ink-2/80']">
                                <option value="">Select sponsorship package / area</option>
                                <option v-for="o in content.form.interests" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                            </select>
                            <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Estimated Budget / Range" name="budget" :error="form.errors.budget">
                        <div class="relative">
                            <select :id="id" v-model="form.budget" name="budget" :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'appearance-none pr-11', !form.budget && 'text-ink-2/80']">
                                <option value="">Select your budget range</option>
                                <option v-for="o in content.form.budgets" :key="o.value" :value="o.value" class="text-night">{{ o.label }}</option>
                            </select>
                            <Icon name="chevron" :size="18" class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-night" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Specific Requirements" name="requirements" :error="form.errors.requirements">
                        <div class="relative">
                            <input :id="id" v-model.trim="form.requirements" type="text" name="requirements" maxlength="255" :aria-invalid="invalid" :aria-describedby="describedby" :class="iconInputClass(invalid)" />
                        </div>
                    </Field>
                    <Field v-slot="{ id, describedby, invalid }" label="Additional Message" name="message" :error="form.errors.message" class="sm:col-span-2">
                        <div class="relative">
                            <textarea :id="id" v-model="form.message" name="message" rows="3" maxlength="500" placeholder="Write your message or any additional details" :aria-invalid="invalid" :aria-describedby="describedby" :class="[iconInputClass(invalid), 'min-h-24 py-3']" />
                        </div>
                        <p class="mt-1.5 text-right text-sm text-ink-2 tabular-nums">{{ form.message.length }} / 500</p>
                    </Field>
                </div>

                <div class="mt-4 grid gap-3">
                    <label class="flex cursor-pointer items-center gap-3 text-night">
                        <input v-model="form.callback_requested" type="checkbox" name="callback_requested" class="size-5 shrink-0 accent-[var(--green)]" />
                        Please call me back to discuss
                    </label>
                    <ConsentField v-model="form.consent" :consent="content.form.consent" :error="form.errors.consent" />
                </div>

                <button
                    type="submit"
                    class="mx-auto mt-9 flex min-h-14 w-full max-w-[50rem] items-center justify-center gap-3 rounded-xl bg-gradient-to-b from-[#f2bd3e] to-[#e3a92e] px-6 text-lg font-bold text-night shadow-[0_14px_30px_-16px_rgb(180_120_20/0.7)] transition-[filter,transform] duration-300 hover:brightness-105 active:translate-y-px disabled:opacity-70 md:text-xl"
                    :disabled="form.processing"
                >
                    {{ form.processing ? t('inner.form.sending') : 'Submit Sponsorship Enquiry' }}
                    <svg width="18" height="18" viewBox="0 0 14 14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11 11 3M5 3h6v6" /></svg>
                </button>
            </form>
        </FormPage>

        <section id="tiers" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.tiers" />
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(tier, i) in content.tiers" :key="tier.slug" data-reveal="fade"><TierCard :tier="tier" :rank="i + 1" :tone="tier.tone" :featured="i === 0" /></li>
                </ul>
                <div class="mt-6 grid items-center gap-4 md:grid-cols-2">
                    <p class="in-meta">{{ content.tiersNote }}</p>
                    <DownloadCard :item="content.prospectus" tone="tone-sun" />
                </div>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.why" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="w in content.why" :key="w.title" data-reveal="fade"><TintCard :title="w.title" :text="w.text" :icon="w.icon" :tone="w.tone" /></li>
                </ul>
            </div>
        </section>
    </div>
</template>
