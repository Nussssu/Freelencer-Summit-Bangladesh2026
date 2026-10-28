<script setup lang="ts">
import { computed, useId } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Label + help + inline error around any control. The slot receives the ids and aria wiring:
 * <Field v-slot="{ id, describedby, invalid }"> … </Field>. Labels are always visible (no placeholder-only labels).
 */
const props = defineProps<{ label: string; name: string; required?: boolean; help?: string; error?: string; as?: 'div' | 'fieldset' }>();
const { t } = useI18n();
const uid = useId();
const id = computed(() => `f-${props.name}-${uid}`);
const describedby = computed(() => [props.help && `${id.value}-help`, props.error && `${id.value}-err`].filter(Boolean).join(' ') || undefined);
</script>

<template>
    <component :is="as ?? 'div'" :data-field="name" class="min-w-0">
        <component :is="as === 'fieldset' ? 'legend' : 'label'" :for="as === 'fieldset' ? undefined : id" class="block text-base font-medium text-night">
            {{ label }}
            <span v-if="required" class="text-red-ink" aria-hidden="true">*</span>
            <span v-else class="text-sm font-normal text-ink-2">({{ t('inner.form.optional') }})</span>
        </component>
        <p v-if="help" :id="`${id}-help`" class="mt-1 text-sm text-ink-2">{{ help }}</p>
        <div class="mt-2"><slot :id="id" :describedby="describedby" :invalid="!!error" /></div>
        <p v-if="error" :id="`${id}-err`" class="mt-1.5 text-sm font-medium text-red-ink">{{ error }}</p>
    </component>
</template>
