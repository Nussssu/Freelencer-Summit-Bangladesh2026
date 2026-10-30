<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import SelectField from '@/components/forms/SelectField.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import TextField from '@/components/forms/TextField.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TierCard from '@/components/inner/TierCard.vue';
import TintCard from '@/components/inner/TintCard.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, DownloadItem, InnerPage, Option, SectionHeading, Tier } from '@/types/inner';

// Brief §10: why sponsor, tiers (provisional), prospectus download and the enquiry form (SPO reference, call-back option).
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'why' | 'tiers' | 'enquiry', SectionHeading>;
        why: { icon: GlyphName; tone: string; title: string; text: string }[];
        tiers: (Tier & { tone: string })[];
        tiersNote: string;
        prospectus: DownloadItem;
        form: { interests: Option[]; budgets: Option[]; consent: Consent };
        aside: string[];
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const form = useForm({ company: '', contact_person: '', designation: '', email: '', mobile: '', interest: '', budget: '', requirements: '', message: '', callback_requested: false, consent: false, hp_company: '' });
const { reference, reveal } = useSubmitted('sponsor', '#enquiry');
const submit = () => form.post('/sponsorship-opportunity', { preserveScroll: true, onSuccess: () => (form.reset(), reveal()) });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.why" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="w in content.why" :key="w.title" data-reveal="fade"><TintCard :title="w.title" :text="w.text" :icon="w.icon" :tone="w.tone" /></li>
                </ul>
            </div>
        </section>

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

        <section id="enquiry" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-4">
                    <SectionHead :heading="content.headings.enquiry" />
                    <ul class="in-ticks text-ink-2">
                        <li v-for="a in content.aside" :key="a">{{ a }}</li>
                    </ul>
                </div>
                <div class="lg:col-span-8">
                    <FormSuccess v-if="reference" title="Enquiry received" :reference="reference" reference-label="Your reference number" body="The partnerships team will contact you with the prospectus and next steps.">
                        <InBtn href="/sponsors" tone="ghost">See current sponsors</InBtn>
                    </FormSuccess>
                    <div v-else class="in-form">
                        <StepShell :form="form" submit-label="Send enquiry" @submit="submit">
                            <fieldset>
                                <legend class="in-group">Your organisation</legend>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <TextField v-model="form.company" label="Company" name="company" required autocomplete="organization" :error="form.errors.company" class="sm:col-span-2" />
                                    <TextField v-model="form.contact_person" label="Contact person" name="contact_person" required autocomplete="name" :error="form.errors.contact_person" />
                                    <TextField v-model="form.designation" label="Designation" name="designation" :error="form.errors.designation" />
                                    <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                                    <TextField v-model="form.mobile" label="Phone" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="in-group">Your interest</legend>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <SelectField v-model="form.interest" label="Sponsorship interest" name="interest" :options="content.form.interests" required :error="form.errors.interest" />
                                    <SelectField v-model="form.budget" label="Budget range" name="budget" :options="content.form.budgets" :error="form.errors.budget" />
                                    <TextareaField v-model="form.requirements" label="Requirements" name="requirements" :max="2000" :rows="3" :error="form.errors.requirements" class="sm:col-span-2" />
                                    <TextareaField v-model="form.message" label="Message" name="message" :max="2000" :rows="3" :error="form.errors.message" class="sm:col-span-2" />
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-night/15 bg-snow p-4 sm:col-span-2">
                                        <input v-model="form.callback_requested" type="checkbox" name="callback_requested" class="size-5 shrink-0 accent-[var(--green)]" />
                                        <span>Please call me back to discuss</span>
                                    </label>
                                    <ConsentField v-model="form.consent" :consent="content.form.consent" :error="form.errors.consent" class="sm:col-span-2" />
                                </div>
                            </fieldset>
                        </StepShell>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
