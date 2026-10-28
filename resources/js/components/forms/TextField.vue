<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Field from './Field.vue';
import { inputClass, stateClass } from './input';

// text | email | url | mobile (Bangladesh: 01XXXXXXXXX or +8801XXXXXXXXX; the server validates and normalises).
const props = withDefaults(defineProps<{ label: string; name: string; type?: 'text' | 'email' | 'url' | 'mobile'; required?: boolean; help?: string; error?: string; autocomplete?: string; maxlength?: number }>(), { type: 'text' });
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
const native = computed(() => (props.type === 'mobile' ? 'tel' : props.type));
const auto = computed(() => props.autocomplete ?? { text: undefined, email: 'email', url: 'url', mobile: 'tel' }[props.type]);
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" :label="label" :name="name" :required="required" :help="help ?? (type === 'mobile' ? t('inner.form.mobileHelp') : undefined)" :error="error">
        <input
            :id="id"
            v-model.trim="model"
            :type="native"
            :name="name"
            :inputmode="type === 'mobile' ? 'tel' : undefined"
            :autocomplete="auto"
            :required="required"
            :maxlength="maxlength"
            :aria-invalid="invalid"
            :aria-describedby="describedby"
            :class="[inputClass, stateClass(invalid)]"
        />
    </Field>
</template>
