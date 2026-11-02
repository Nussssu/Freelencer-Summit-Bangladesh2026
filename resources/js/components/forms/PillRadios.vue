<script setup lang="ts">
import type { Option } from '@/types/inner';
import Field from './Field.vue';

// Single choice as a row of compact radio cards (the mock-ups' attendee and profile type); the chosen one turns light teal.
withDefaults(defineProps<{ label: string; name: string; options: Option[]; required?: boolean; error?: string; columns?: string }>(), { columns: 'sm:grid-cols-3 lg:grid-cols-5' });
const model = defineModel<string>({ default: '' });
</script>

<template>
    <Field as="fieldset" :label="label" :name="name" :required="required" :error="error">
        <div class="grid grid-cols-2 gap-3 lg:gap-4" :class="columns">
            <label
                v-for="o in options"
                :key="o.value"
                class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border px-3.5 transition-colors duration-200 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-green/25 sm:px-5"
                :class="model === o.value ? 'border-transparent bg-[#dff3f3]' : 'border-night/15 bg-white hover:border-night/30'"
            >
                <input v-model="model" type="radio" :name="name" :value="o.value" class="sr-only" />
                <span class="grid size-5 shrink-0 place-items-center rounded-full border-[1.5px]" :class="model === o.value ? 'border-green bg-green' : 'border-night/60'" aria-hidden="true"><span v-if="model === o.value" class="size-2 rounded-full bg-[#dff3f3]" /></span>
                <span class="text-night">{{ o.label }}</span>
            </label>
        </div>
    </Field>
</template>
