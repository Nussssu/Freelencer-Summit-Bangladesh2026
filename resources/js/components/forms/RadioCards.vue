<script setup lang="ts">
import type { Option } from '@/types/inner';
import Field from './Field.vue';

// Single choice as cards with a description (award categories, nominee type). Disabled options stay visible.
withDefaults(defineProps<{ label: string; name: string; options: Option[]; required?: boolean; help?: string; error?: string; columns?: string }>(), { columns: 'sm:grid-cols-2' });
const model = defineModel<string>({ default: '' });
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" as="fieldset" :label="label" :name="name" :required="required" :help="help" :error="error">
        <div class="grid gap-3" :class="columns" :aria-describedby="describedby">
            <label v-for="o in options" :key="o.value" class="group relative flex gap-3 rounded-md border bg-snow p-4 transition-[border-color,box-shadow] duration-200" :class="[o.disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer hover:border-night/40', model === o.value ? 'border-green ring-3 ring-green/15' : invalid ? 'border-red' : 'border-night/15']">
                <input :id="`${id}-${o.value}`" v-model="model" type="radio" :name="name" :value="o.value" :disabled="o.disabled" :required="required" class="mt-1 size-4 shrink-0 accent-[var(--green)]" />
                <span class="min-w-0">
                    <span class="block font-semibold">{{ o.label }}</span>
                    <span v-if="o.description" class="mt-1 block text-sm text-ink-2">{{ o.description }}</span>
                </span>
            </label>
        </div>
    </Field>
</template>
