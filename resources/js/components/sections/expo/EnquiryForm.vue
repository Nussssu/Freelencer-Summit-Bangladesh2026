<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { utm } from '@/composables/useUtm';
import type { SharedProps } from '@/types/content';

// BPO's 2-step "Enquire to Exhibit" (B7): company info, then personal info. Posts to Laravel; errors render inline.
const { t } = useI18n();
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
            <button type="button" class="btn btn-ghost mt-6" @click="step = 1">{{ t('form.another') }}</button>
        </div>
        <form v-else novalidate @submit.prevent="step === 1 ? next() : submit()">
            <ol class="mb-6 grid grid-cols-2 gap-2" aria-hidden="true">
                <li v-for="n in [1, 2]" :key="n" class="t-label border-t-2 pt-2" :class="step >= n ? 'border-green text-night' : 'border-line-light text-ink-2'">{{ n }} · {{ t(n === 1 ? 'form.company' : 'form.personal') }}</li>
            </ol>
            <p class="sr-only" aria-live="polite">{{ t('form.step', { n: step }) }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div v-for="f in fields" :key="f.key" :class="f.key === 'industry' || f.key === 'head_office' ? 'sm:col-span-2' : ''">
                    <label :for="`enq-${f.key}`" class="t-label text-ink-2">
                        {{ t(`form.${f.key}`) }} <span v-if="!f.required" class="normal-case">({{ t('form.optional') }})</span><span v-else aria-hidden="true" class="text-red">*</span>
                    </label>
                    <input
                        :id="`enq-${f.key}`"
                        v-model="form[f.key as Key]"
                        :type="f.type"
                        :name="f.key"
                        :autocomplete="f.auto"
                        :required="f.required"
                        :aria-invalid="!!form.errors[f.key as Key]"
                        :aria-describedby="form.errors[f.key as Key] ? `enq-${f.key}-err` : undefined"
                        class="mt-1 block min-h-11 w-full rounded-[2px] border bg-paper px-3 py-2 text-night"
                        :class="form.errors[f.key as Key] ? 'border-red' : 'border-night/30 focus:border-green'"
                    />
                    <p v-if="form.errors[f.key as Key]" :id="`enq-${f.key}-err`" class="mt-1 text-sm font-medium text-red-ink">{{ form.errors[f.key as Key] }}</p>
                </div>
            </div>
            <div class="mt-6 flex gap-2">
                <button v-if="step === 2" type="button" class="btn btn-ghost" @click="step = 1">{{ t('form.back') }}</button>
                <button type="submit" class="btn btn-green" :disabled="form.processing">{{ step === 1 ? t('form.next') : form.processing ? t('form.sending') : t('form.submit') }}</button>
            </div>
        </form>
    </div>
</template>
