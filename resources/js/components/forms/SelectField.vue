<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { Option, OptionGroup } from '@/types/inner';
import Field from './Field.vue';
import { inputClass, stateClass } from './input';
import { usePlaceholder } from '@/composables/usePlaceholder';

// Native select (best on mobile); `groups` renders <optgroup>s, e.g. districts by division.
defineProps<{ label: string; name: string; options?: Option[]; groups?: OptionGroup[]; required?: boolean; help?: string; error?: string; placeholder?: string }>();
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
const ph = usePlaceholder();
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" :label="label" :name="name" :required="required" :help="help" :error="error">
        <select :id="id" v-model="model" :name="name" :required="required" :aria-invalid="invalid" :aria-describedby="describedby" :class="[inputClass, stateClass(invalid), 'appearance-none bg-[url(&quot;data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%2712%27 fill=%27none%27 stroke=%27%23071411%27 stroke-width=%271.6%27%3E%3Cpath d=%27M2 4l4 4 4-4%27/%3E%3C/svg%3E&quot;)] bg-[position:right_14px_center] bg-no-repeat pr-10']">
            <option value="">{{ placeholder ?? ph(name, t('inner.form.choose')) }}</option>
            <option v-for="o in options" :key="o.value" :value="o.value" :disabled="o.disabled">{{ o.label }}</option>
            <optgroup v-for="g in groups" :key="g.label" :label="g.label">
                <option v-for="o in g.options" :key="o.value" :value="o.value">{{ o.label }}</option>
            </optgroup>
        </select>
    </Field>
</template>
