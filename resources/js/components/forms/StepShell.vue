<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ErrorSummary from './ErrorSummary.vue';

/**
 * <form> wrapper for every journey: progress (hidden for one step), error summary, honeypot,
 * Back / Continue / Submit. The page validates a step before emitting `next`; the last step emits `submit`.
 */
const props = defineProps<{ form: InertiaForm<object>; steps?: string[]; submitLabel?: string }>();
const step = defineModel<number>('step', { default: 0 });
const emit = defineEmits<{ next: []; submit: [] }>();
const { t } = useI18n();
const total = () => props.steps?.length ?? 1;
const last = () => step.value >= total() - 1;
</script>

<template>
    <form novalidate class="space-y-8" @submit.prevent="last() ? emit('submit') : emit('next')">
        <div v-if="steps && steps.length > 1">
            <p class="sr-only" aria-live="polite">{{ t('inner.form.step', { n: step + 1, total: steps.length }) }}: {{ steps[step] }}</p>
            <ol class="flex flex-wrap items-center gap-x-3 gap-y-2" aria-hidden="true">
                <template v-for="(s, i) in steps" :key="s">
                    <li v-if="i" class="h-px w-6 bg-night/15 sm:w-10" />
                    <li class="flex items-center gap-2.5 text-sm font-medium" :class="step >= i ? 'text-night' : 'text-ink-2'">
                        <span class="grid size-7 place-items-center rounded-full border text-xs font-semibold" :class="step >= i ? 'border-green bg-green text-paper' : 'border-night/20 bg-snow'">{{ i + 1 }}</span>
                        <span :class="step === i ? '' : 'max-sm:hidden'">{{ s }}</span>
                    </li>
                </template>
            </ol>
        </div>
        <ErrorSummary :errors="form.errors as Record<string, string>" />
        <!-- Honeypot: humans never see or fill it (App\Http\Middleware\Honeypot). -->
        <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
            <label>{{ t('inner.form.honeypot') }} <input v-model="(form as any).hp_company" type="text" name="hp_company" tabindex="-1" autocomplete="off" /></label>
        </div>
        <slot />
        <div class="flex flex-wrap items-center gap-3 border-t border-line-light pt-6">
            <button v-if="step > 0" type="button" class="btn btn-ghost [clip-path:none]" @click="step--">{{ t('inner.form.back') }}</button>
            <button type="submit" class="btn btn-green ml-auto [clip-path:none]" :disabled="form.processing">
                {{ !last() ? t('inner.form.continue') : form.processing ? t('inner.form.sending') : (submitLabel ?? t('inner.form.submit')) }}
            </button>
        </div>
    </form>
</template>
