<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsentField from '@/components/forms/ConsentField.vue';
import FormSuccess from '@/components/forms/FormSuccess.vue';
import StepShell from '@/components/forms/StepShell.vue';
import TextField from '@/components/forms/TextField.vue';
import DetailLayout from '@/components/inner/DetailLayout.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import StatusBadge from '@/components/inner/StatusBadge.vue';
import { useSubmitted } from '@/components/inner/useSubmitted';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { Consent, InnerPage, ProgramCard } from '@/types/inner';

// Brief §15: each activity's page: date, venue, organiser/partner, description, speakers, registration and photo/video gallery.
const props = defineProps<{
    page: InnerPage;
    content: {
        program: ProgramCard & {
            description: string | null;
            speakers: { slug: string; name: string; designation: string | null; organisation: string | null; role: string | null; photo: string | null }[];
            media: { kind: string; src: string | null; youtubeId: string | null; caption: string | null }[];
        };
        typeLabel: string;
        facts: { icon: GlyphName; label: string; value: string }[];
        register: { open: boolean; action: string; closed: string; consent: Consent };
        related: ProgramCard[];
        backHref: string;
        backLabel: string;
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const form = useForm({ name: '', email: '', mobile: '', institution: '', consent: false, hp_company: '' });
const { reference, reveal } = useSubmitted('program', '#register');
const submit = () => form.post(props.content.register.action, { preserveScroll: true, onSuccess: () => (form.reset(), reveal()) });
const date = (iso: string | null) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Dhaka' }) : t('inner.tbc.toBeConfirmed'));
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <StatusBadge :status="content.program.status" :label="content.program.statusLabel" />
                <span class="in-pill">{{ content.typeLabel }}</span>
            </div>
        </template>
    </InnerHero>

    <div ref="root">
        <DetailLayout aside-label="Programme details">
            <h2 class="in-h3 text-2xl!">About this programme</h2>
            <div v-if="content.program.description" class="in-prose mt-4" v-html="content.program.description" />
            <p v-else class="in-body mt-4">The full programme, speakers and mentors are published here as they are confirmed.</p>

            <div v-if="content.program.speakers.length" class="mt-12">
                <h2 class="in-h3 text-2xl!">Speakers and mentors</h2>
                <ul class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <li v-for="s in content.program.speakers" :key="s.slug" class="overflow-hidden rounded-xl bg-white ring-1 ring-night/8">
                        <span class="block aspect-[4/5] bg-mist"><img v-if="s.photo" :src="s.photo" :alt="s.name" loading="lazy" class="h-full w-full object-cover object-top" /></span>
                        <span class="block p-4">
                            <span v-if="s.role" class="in-meta block">{{ s.role }}</span>
                            <span class="mt-0.5 block font-extrabold text-night">{{ s.name }}</span>
                            <span v-if="s.designation || s.organisation" class="mt-1 block text-sm text-ink-2">{{ [s.designation, s.organisation].filter(Boolean).join(', ') }}</span>
                        </span>
                    </li>
                </ul>
            </div>

            <div id="register" class="mt-10 scroll-mt-[calc(var(--header-h)+1rem)]">
                <h2 class="in-h3 text-2xl!">Register for this programme</h2>
                <FormSuccess v-if="reference" class="mt-5" title="You're registered" :reference="reference" reference-label="Your reference number" body="We will email the final time and venue details before the programme." />
                <div v-else-if="content.register.open" class="in-form mt-5">
                    <StepShell :form="form" submit-label="Register" @submit="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <TextField v-model="form.name" label="Full name" name="name" required autocomplete="name" :error="form.errors.name" />
                            <TextField v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                            <TextField v-model="form.mobile" label="Mobile number" name="mobile" type="mobile" required :help="t('inner.form.mobileHelp')" :error="form.errors.mobile" />
                            <TextField v-model="form.institution" label="Institution or organisation" name="institution" :error="form.errors.institution" />
                            <ConsentField v-model="form.consent" :consent="content.register.consent" :error="form.errors.consent" class="sm:col-span-2" />
                        </div>
                    </StepShell>
                </div>
                <div v-else class="in-card tone-sun is-washed mt-5 flex-row! flex-wrap items-center gap-4 p-5">
                    <span class="in-disc w-11!"><Glyph name="clock" /></span>
                    <p class="min-w-0 flex-1 text-ink-2">{{ content.register.closed }}</p>
                    <InBtn href="/register" tone="gold">Register for the Grand Summit</InBtn>
                </div>
            </div>

            <div class="mt-12">
                <h2 class="in-h3 text-2xl!">Photos and videos</h2>
                <ul v-if="content.program.media.length" class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                    <li v-for="(m, i) in content.program.media" :key="i" class="in-media is-round aspect-[4/3]">
                        <img v-if="m.src" :src="m.src" :alt="m.caption ?? ''" loading="lazy" />
                    </li>
                </ul>
                <EmptyState v-else class="mt-5" level="h3" icon="image" title="Gallery after the programme" body="Photos and videos are added here once the programme has taken place." />
            </div>

            <template #aside>
                <dl class="space-y-4">
                    <div v-for="f in content.facts" :key="f.label" class="flex items-center gap-3 tone-teal">
                        <span class="in-disc w-11!"><Glyph :name="f.icon" /></span>
                        <div class="min-w-0">
                            <dt class="in-meta">{{ f.label }}</dt>
                            <dd class="font-bold text-night">{{ f.value }}</dd>
                        </div>
                    </div>
                </dl>
                <div class="flex flex-wrap gap-3 border-t border-night/8 pt-5">
                    <a v-if="content.register.open" href="#register" class="in-btn is-gold">Register</a>
                    <ShareButton :title="content.program.title" />
                </div>
                <Link :href="content.backHref" class="in-link">← {{ content.backLabel }}</Link>
            </template>

            <template v-if="content.related.length" #related>
                <SectionHead :heading="{ title: 'More programmes', emphasis: 'programmes' }" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="r in content.related.slice(0, 3)" :key="r.slug"><InfoCard :title="r.title" :href="r.href" :image="r.image ?? '/media/photos/gallery-09.jpg'" :meta="[date(r.startsAt), r.location]" :status="{ value: r.status, label: r.statusLabel }" /></li>
                </ul>
            </template>
        </DetailLayout>
    </div>
</template>
