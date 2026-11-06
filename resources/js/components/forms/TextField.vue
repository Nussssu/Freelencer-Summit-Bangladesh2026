<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import BdPrefix from './BdPrefix.vue';
import Field from './Field.vue';
import { inputClass, stateClass } from './input';
import { usePlaceholder } from '@/composables/usePlaceholder';

// text | email | url | mobile (Bangladesh, after a fixed +880 prefix: 1XXXXXXXXX; the server validates and normalises).
const props = withDefaults(defineProps<{ label: string; name: string; type?: 'text' | 'email' | 'url' | 'mobile'; required?: boolean; help?: string; error?: string; autocomplete?: string; maxlength?: number; placeholder?: string }>(), { type: 'text' });
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
const ph = usePlaceholder();
const native = computed(() => (props.type === 'mobile' ? 'tel' : props.type));
const auto = computed(() => props.autocomplete ?? { text: undefined, email: 'email', url: 'url', mobile: 'tel' }[props.type]);
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" :label="label" :name="name" :required="required" :help="help ?? (type === 'mobile' ? t('inner.form.mobileHelp') : undefined)" :error="error">
        <div class="relative">
            <BdPrefix v-if="type === 'mobile'" class="left-3.5!" />
            <input
                :id="id"
                v-model.trim="model"
                :type="native"
                :name="name"
                :inputmode="type === 'mobile' ? 'tel' : undefined"
                :autocomplete="auto"
                :required="required"
                :maxlength="maxlength"
                :placeholder="placeholder ?? ph(name)"
                :aria-invalid="invalid"
                :aria-describedby="describedby"
                :class="[inputClass, stateClass(invalid), type === 'mobile' && 'pl-[6.5rem]!']"
            />
        </div>
    </Field>
</template>
