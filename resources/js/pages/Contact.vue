<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AboutHero from '@/components/about/AboutHero.vue';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import SelectField from '@/components/forms/SelectField.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextareaField from '@/components/forms/TextareaField.vue';
import TextField from '@/components/forms/TextField.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, Option } from '@/types/inner';

// Contact, after the About page and the reference's Key Contacts: the About-style photo hero, a card per team (team,
// optional name and role, email and phone, or TBA until confirmed), then one form routed by topic (CON reference,
// copy to the organiser inbox).
const props = defineProps<{
    page: InnerPage;
    content: {
        topics: Option[];
        topic: string;
        consent: Consent;
        contacts: { label: string; value: string | null; name?: string | null; role?: string | null; phone?: string | null; icon: GlyphName; tone: string }[];
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
    <AboutHero :page="page" />

    <div ref="root" class="ground on-light">
        <!-- Key contacts: one card per team -->
        <section class="in-section" aria-labelledby="ct-contacts">
            <div class="container-site max-w-[1594px]!">
                <SectionHead id="ct-contacts" :heading="{ title: 'Key Contacts', emphasis: 'Contacts' }" center />
                <p class="-mt-4 mb-10 text-center text-lg text-ink-2 md:mb-12" data-reveal="fade">Reach out to our team for registration, awards, sponsorship, exhibition or general enquiries.</p>
                <ul class="mx-auto flex max-w-[1530px] flex-wrap justify-center gap-6">
                    <li
                        v-for="c in content.contacts"
                        :key="c.label"
                        class="ct-card w-full sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] 2xl:w-[calc((100%-4.5rem)/4)]"
                        data-reveal="fade"
                    >
                        <div class="px-5 pt-7 pb-5 text-center">
                            <h3 class="text-xl leading-snug font-extrabold text-green">{{ c.name || c.label }}</h3>
                            <span class="mx-auto mt-3 block h-[3px] w-9 rounded-full bg-green" aria-hidden="true" />
                            <p v-if="c.role" class="mt-3 text-base text-ink-2">{{ c.role }}</p>
                            <span v-if="c.name" class="ct-pill">{{ c.label }}</span>
                        </div>
                        <ul class="space-y-3 border-t border-night/10 p-[1.125rem]">
                            <li class="ct-row">
                                <span class="ct-icon"><Glyph name="mail" class="size-4" /></span>
                                <div class="min-w-0">
                                    <p class="ct-key">Email</p>
                                    <a v-if="c.value" :href="`mailto:${c.value}`" class="block break-all text-night hover:text-green">{{ c.value }}</a>
                                    <span v-else class="text-ink-2">{{ t('common.tba') }}</span>
                                </div>
                            </li>
                            <li v-if="c.phone" class="ct-row">
                                <span class="ct-icon"><Glyph name="phone" class="size-4" /></span>
                                <div class="min-w-0">
                                    <p class="ct-key">Phone</p>
                                    <a :href="`tel:${c.phone.replace(/[^+\d]/g, '')}`" class="block text-night hover:text-green">{{ c.phone }}</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
                <p class="mt-6 text-center text-sm text-ink-2">{{ content.contactsNote }}</p>
            </div>
        </section>

        <!-- Contact form -->
        <section id="form" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-labelledby="ct-form">
            <div class="container-site max-w-3xl!">
                <SectionHead id="ct-form" :heading="{ title: 'Send us a message', emphasis: 'message' }" center />
                <FormSuccess v-if="reference" title="Message sent" :reference="reference" reference-label="Your reference number" body="Thank you. The right person in the team will reply by email.">
                    <InBtn href="/" tone="ghost">Back to home</InBtn>
                </FormSuccess>
                <div v-else class="in-form" data-reveal="fade">
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
            </div>
        </section>
    </div>
</template>

<style scoped>
/* The About page's ground: ice blue with soft teal and summit-gold glows */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 40%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 90%, rgb(8 127 140 / 0.18), transparent 70%),
        var(--mist);
}

/* Contact card: theme-green top bar, centred name and role, team pill, then email / phone rows */
.ct-card {
    overflow: hidden;
    border-top: 7px solid var(--green);
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 10px 30px -12px rgb(7 20 17 / 0.18);
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}
.ct-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px -14px rgb(7 20 17 / 0.26);
}
.ct-pill {
    display: inline-block;
    margin-top: 0.625rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--green) 12%, transparent);
    color: var(--green);
    font-size: 0.875rem;
    font-weight: 600;
}
.ct-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 0.5rem;
    border-radius: 0.875rem;
    background: color-mix(in srgb, var(--night) 4%, transparent);
    font-size: 0.9375rem;
}
.ct-icon {
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    background: var(--green);
    color: #fff;
}
.ct-key {
    color: var(--green);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
</style>
