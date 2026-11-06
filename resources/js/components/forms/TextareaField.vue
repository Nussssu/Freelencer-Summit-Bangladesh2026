<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Field from './Field.vue';
import { inputClass, stateClass } from './input';
import { usePlaceholder } from '@/composables/usePlaceholder';

// Textarea with a live character counter (announced politely, not on every keystroke for screen readers).
withDefaults(defineProps<{ label: string; name: string; max?: number; rows?: number; required?: boolean; help?: string; error?: string; placeholder?: string }>(), { max: 2000, rows: 5 });
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
const ph = usePlaceholder();
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" :label="label" :name="name" :required="required" :help="help" :error="error">
        <textarea :id="id" v-model="model" :name="name" :rows="rows" :maxlength="max" :placeholder="placeholder ?? ph(name)" :required="required" :aria-invalid="invalid" :aria-describedby="`${describedby ?? ''} ${id}-count`.trim()" :class="[inputClass, stateClass(invalid), 'resize-y']" />
        <p :id="`${id}-count`" class="mt-1 text-right text-sm tabular-nums" :class="model.length > max * 0.9 ? 'text-red-ink' : 'text-ink-2'">{{ t('inner.form.counter', { n: model.length, max }) }}</p>
    </Field>
</template>
