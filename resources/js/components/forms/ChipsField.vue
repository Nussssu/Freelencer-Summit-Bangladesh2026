<script setup lang="ts">
import type { Option } from '@/types/inner';
import Field from './Field.vue';

// Multi-select as toggle chips (real checkboxes, so keyboard and screen readers work natively).
defineProps<{ label: string; name: string; options: Option[]; required?: boolean; help?: string; error?: string }>();
const model = defineModel<string[]>({ default: () => [] });
</script>

<template>
    <Field v-slot="{ id, describedby }" as="fieldset" :label="label" :name="name" :required="required" :help="help" :error="error">
        <div class="flex flex-wrap gap-2" :aria-describedby="describedby">
            <label v-for="o in options" :key="o.value" class="group relative cursor-pointer">
                <input :id="`${id}-${o.value}`" v-model="model" type="checkbox" :name="`${name}[]`" :value="o.value" class="peer sr-only" />
                <span class="inline-flex min-h-11 items-center gap-2 rounded-full border border-night/15 bg-snow px-4 text-sm font-medium transition-colors duration-200 group-hover:border-night/40 peer-checked:border-green peer-checked:bg-green peer-checked:text-paper peer-focus-visible:ring-3 peer-focus-visible:ring-green/30">
                    {{ o.label }}
                </span>
            </label>
        </div>
    </Field>
</template>
