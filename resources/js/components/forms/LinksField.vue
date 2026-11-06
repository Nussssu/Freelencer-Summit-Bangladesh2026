<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import type { LinkRow, Option } from '@/types/inner';
import Field from './Field.vue';
import { inputClass, stateClass } from './input';
import { usePlaceholder } from '@/composables/usePlaceholder';

// Repeatable links (portfolio, marketplace profiles). With `platforms`, each row gets a platform select.
const props = withDefaults(defineProps<{ label: string; name: string; platforms?: Option[]; max?: number; required?: boolean; help?: string; errors?: Record<string, string> }>(), { max: 5, errors: () => ({}) });
const model = defineModel<LinkRow[]>({ default: () => [{ url: '' }] });
const { t } = useI18n();
const ph = usePlaceholder();
const list = ref<HTMLElement>();

async function add() {
    model.value = [...model.value, props.platforms ? { platform: '', url: '' } : { url: '' }];
    await nextTick();
    list.value?.querySelector<HTMLInputElement>('li:last-child input[type=url]')?.focus();
}
const remove = (i: number) => (model.value = model.value.filter((_, j) => j !== i));
const err = (i: number, k: string) => props.errors[`${props.name}.${i}.${k}`];
</script>

<template>
    <Field v-slot="{ id, describedby }" as="fieldset" :label="label" :name="name" :required="required" :help="help" :error="errors[name]">
        <ul ref="list" class="space-y-3" :aria-describedby="describedby">
            <li v-for="(row, i) in model" :key="i" class="flex flex-wrap items-start gap-2 sm:flex-nowrap">
                <select v-if="platforms" v-model="row.platform" :aria-label="`${t('inner.form.platform')} ${i + 1}`" :aria-invalid="!!err(i, 'platform')" :class="[inputClass, stateClass(!!err(i, 'platform')), 'sm:w-48']">
                    <option value="">{{ ph('platform', t('inner.form.platform')) }}</option>
                    <option v-for="p in platforms" :key="p.value" :value="p.value">{{ p.label }}</option>
                </select>
                <div class="min-w-0 flex-1">
                    <input :id="`${id}-${i}`" v-model.trim="row.url" type="url" inputmode="url" :placeholder="ph(name, ph('link', 'https://'))" :aria-label="`${t('inner.form.url')} ${i + 1}`" :aria-invalid="!!err(i, 'url')" :class="[inputClass, stateClass(!!err(i, 'url'))]" />
                    <p v-if="err(i, 'url') || err(i, 'platform')" class="mt-1 text-sm font-medium text-red-ink">{{ err(i, 'url') ?? err(i, 'platform') }}</p>
                </div>
                <button v-if="model.length > 1" type="button" class="grid min-h-11 min-w-11 place-items-center rounded-md border border-night/15 text-ink-2 transition-colors hover:border-red hover:text-red-ink" :aria-label="t('inner.form.removeLink', { n: i + 1 })" @click="remove(i)"><Icon name="close" :size="16" /></button>
            </li>
        </ul>
        <button v-if="model.length < max" type="button" class="mt-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-green hover:underline" @click="add"><Icon name="plus" :size="16" /> {{ t('inner.form.addLink') }}</button>
    </Field>
</template>
