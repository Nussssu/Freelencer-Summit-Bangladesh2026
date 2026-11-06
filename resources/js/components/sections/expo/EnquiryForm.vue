<script setup lang="ts">
import { usePlaceholder } from '@/composables/usePlaceholder';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { utm } from '@/composables/useUtm';
import type { SharedProps } from '@/types/content';

// BPO's 2-step "Enquire to Exhibit" (B7): company info, then personal info. Posts to Laravel; errors render inline.
const { t } = useI18n();
const ph = usePlaceholder();
const page = usePage<SharedProps>();
const step = ref<1 | 2 | 'done'>(1);
const root = ref<HTMLElement>();

const form = useForm({ company_name: '', website: '', industry: '', head_office: '', full_name: '', designation: '', email: '', phone: '', utm: {} as Record<string, string> });
const steps = {
    1: [
        { key: 'company_name', type: 'text', auto: 'organization', required: true },
        { key: 'website', type: 'url', auto: 'url', required: false },
        { key: 'industry', type: 'text', auto: 'off', required: true },
        { key: 'head_office', type: 'text', auto: 'address-level2', required: true },
    ],
    2: [
        { key: 'full_name', type: 'text', auto: 'name', required: true },
        { key: 'designation', type: 'text', auto: 'organization-title', required: true },
        { key: 'email', type: 'email', auto: 'email', required: true },
        { key: 'phone', type: 'tel', auto: 'tel', required: true },
    ],
} as const;
type Key = (typeof steps)[1][number]['key'] | (typeof steps)[2][number]['key'];
const fields = computed(() => (step.value === 'done' ? [] : steps[step.value]));

const focusFirst = async (sel = 'input') => (await nextTick(), root.value?.querySelector<HTMLElement>(sel)?.focus());

function next() {
    step.value = 2;
    focusFirst();
}
function submit() {
    form.utm = utm.value;
    form.post('/exhibition/enquiries', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            step.value = 'done';
            form.reset();
            focusFirst('[data-success]');
        },
        onError: (errors) => {
            if (steps[1].some((f) => f.key in errors)) step.value = 1;
            focusFirst('[aria-invalid="true"]');
        },
    });
}
</script>

<template>
    <div ref="root">
        <div v-if="step === 'done'" data-success tabindex="-1" class="py-6" role="status">
            <p class="t-label text-green">{{ t('form.success') }}</p>
            <p class="mt-3 text-lg">{{ page.props.flash.success }}</p>
            <button type="button" class="btn btn-ghost mt-6 [clip-path:none]" @click="step = 1">{{ t('form.another') }}</button>
        </div>
        <form v-else novalidate @submit.prevent="step === 1 ? next() : submit()">
            <ol class="mb-8 flex items-center gap-3" aria-hidden="true">
                <template v-for="n in [1, 2]" :key="n">
                    <li v-if="n > 1" class="h-px w-10 bg-night/15" />
                    <li class="flex items-center gap-2.5 text-sm font-medium" :class="step >= n ? 'text-night' : 'text-ink-2'">
                        <span class="grid size-7 place-items-center rounded-full border text-xs font-semibold" :class="step >= n ? 'border-green bg-green text-paper' : 'border-night/20 bg-snow'">{{ n }}</span>
                        {{ t(n === 1 ? 'form.company' : 'form.personal') }}
                    </li>
                </template>
            </ol>
            <p class="sr-only" aria-live="polite">{{ t('form.step', { n: step }) }}</p>
            <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
                <div v-for="f in fields" :key="f.key" :class="f.key === 'industry' || f.key === 'head_office' ? 'sm:col-span-2' : ''">
                    <label :for="`enq-${f.key}`" class="text-sm font-medium text-night">
                        {{ t(`form.${f.key}`) }} <span v-if="!f.required" class="font-normal text-ink-2">({{ t('form.optional') }})</span><span v-else aria-hidden="true" class="text-red">*</span>
                    </label>
                    <input
                        :id="`enq-${f.key}`"
                        v-model="form[f.key as Key]"
                        :type="f.type"
                        :name="f.key"
                        :placeholder="ph(f.key)"
                        :autocomplete="f.auto"
                        :required="f.required"
                        :aria-invalid="!!form.errors[f.key as Key]"
                        :aria-describedby="form.errors[f.key as Key] ? `enq-${f.key}-err` : undefined"
                        class="mt-1.5 block min-h-11 w-full rounded-md border bg-snow px-3.5 py-2.5 text-night shadow-[0_1px_2px_rgb(7_20_17/0.04)] transition-[border-color,box-shadow] duration-200 outline-none focus:ring-3"
                        :class="form.errors[f.key as Key] ? 'border-red focus:ring-red/15' : 'border-night/15 hover:border-night/30 focus:border-green focus:ring-green/15'"
                    />
                    <p v-if="form.errors[f.key as Key]" :id="`enq-${f.key}-err`" class="mt-1 text-sm font-medium text-red-ink">{{ form.errors[f.key as Key] }}</p>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3 border-t border-line-light pt-6">
                <button v-if="step === 2" type="button" class="btn btn-ghost mr-auto [clip-path:none]" @click="step = 1">{{ t('form.back') }}</button>
                <button type="submit" class="btn btn-green [clip-path:none]" :disabled="form.processing">{{ step === 1 ? t('form.next') : form.processing ? t('form.sending') : t('form.submit') }}</button>
            </div>
        </form>
    </div>
</template>
