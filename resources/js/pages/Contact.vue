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
import FormLayout from '@/components/inner/FormLayout.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, Option } from '@/types/inner';

// Contact: one form routed by topic (CON reference, copy to the organiser inbox) and a contact per journey.
const props = defineProps<{
    page: InnerPage;
    content: {
        topics: Option[];
        topic: string;
        consent: Consent;
        contacts: { label: string; value: string | null; icon: GlyphName; tone: string }[];
        organisers: { short: string; name: string; role: string; logo: string | null }[];
        contactsNote: string;
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const form = useForm({ topic: props.content.topic, name: '', email: '', mobile: '', message: '', consent: false, hp_company: '' });
const { reference, reveal } = useSubmitted('contact');
const submit = () => form.post('/contact', { preserveScroll: true, onSuccess: () => (form.reset(), reveal()) });
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <FormLayout title="Contacts by topic" icon="chat" help="Prefer to register directly? Go to summit registration." help-href="/register">
            <FormSuccess v-if="reference" title="Message sent" :reference="reference" reference-label="Your reference number" body="Thank you. The right person in the team will reply by email.">
                <InBtn href="/" tone="ghost">Back to home</InBtn>
            </FormSuccess>
            <div v-else class="in-form">
                <StepShell :form="form" submit-label="Send message" @submit="submit">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <SelectField v-model="form.topic" label="Topic" name="topic" :options="content.topics" required :error="form.errors.topic" class="sm:col-span-2" />
                        <TextField v-model="form.name" label="Full name" name="name" required autocomplete="name" :error="form.errors.name" />
                        <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                        <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" class="sm:col-span-2" />
                        <TextareaField v-model="form.message" label="Message" name="message" :max="2000" required :error="form.errors.message" class="sm:col-span-2" />
                        <ConsentField v-model="form.consent" :consent="content.consent" :error="form.errors.consent" class="sm:col-span-2" />
                    </div>
                </StepShell>
            </div>
            <template #aside>
                <ul class="mt-5 space-y-3">
                    <li v-for="c in content.contacts" :key="c.label" class="flex items-center gap-3" :class="c.tone">
                        <span class="in-disc w-10!"><Glyph :name="c.icon" /></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-night">{{ c.label }}</span>
                            <a v-if="c.value" :href="`mailto:${c.value}`" class="text-sm text-green">{{ c.value }}</a>
                            <span v-else class="text-sm text-ink-2">{{ t('common.tba') }}</span>
                        </span>
                    </li>
                </ul>
                <p class="mt-5 text-sm text-ink-2">{{ content.contactsNote }}</p>
            </template>
        </FormLayout>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="{ title: 'Jointly organised by', emphasis: 'organised by' }" />
                <ul class="grid gap-5 md:grid-cols-2">
                    <li v-for="o in content.organisers" :key="o.short" class="in-card is-solid flex-row! items-center gap-5 p-5" data-reveal="fade">
                        <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border border-night/8 bg-snow p-2">
                            <img v-if="o.logo" :src="o.logo" :alt="o.short" class="max-h-full w-auto" />
                            <span v-else class="text-2xl font-extrabold text-bacco">{{ o.short }}</span>
                        </span>
                        <span>
                            <span class="in-meta">{{ o.role }}</span>
                            <span class="in-h3 mt-1 block text-lg!">{{ o.name }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>
