<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Glyph from '@/components/inner/Glyph.vue';

// Numbered progress bar for the multi-step forms: the current step is filled, finished steps show a tick and can be revisited.
defineProps<{ steps: string[] }>();
const step = defineModel<number>({ required: true });
const { t } = useI18n();
</script>

<template>
    <p class="sr-only" aria-live="polite">{{ t('inner.form.step', { n: step + 1, total: steps.length }) }}: {{ steps[step] }}</p>
    <ol class="grid border-b border-line-light pb-7 md:px-4" :style="{ gridTemplateColumns: `repeat(${steps.length}, minmax(0, 1fr))` }">
        <li v-for="(s, i) in steps" :key="s" class="relative flex flex-col items-center text-center">
            <span v-if="i < steps.length - 1" class="absolute top-6 left-1/2 h-px w-full bg-night/15" aria-hidden="true" />
            <button
                type="button"
                class="relative grid size-11 place-items-center rounded-full border text-lg font-semibold transition-colors md:size-12"
                :class="i === step ? 'border-[#0b5560] bg-[#0b5560] text-white' : i < step ? 'border-green bg-[#dff3f3] text-green' : 'border-night/20 bg-white text-night'"
                :disabled="i >= step"
                :aria-label="i < step ? `Back to step ${i + 1}: ${s}` : undefined"
                :aria-current="i === step ? 'step' : undefined"
                @click="step = i"
            >
                <Glyph v-if="i < step" name="check" class="size-5" />
                <template v-else>{{ i + 1 }}</template>
            </button>
            <span class="mt-3 hidden text-sm sm:block md:text-base" :class="i === step ? 'font-bold text-night' : 'text-night/80'">{{ s }}</span>
        </li>
    </ol>
</template>
