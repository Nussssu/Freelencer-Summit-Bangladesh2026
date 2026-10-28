<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import Field from './Field.vue';

// Private document upload: PDF/JPG/PNG, size and count checked here for fast feedback; the server re-checks (App\Support\Uploads).
const props = withDefaults(defineProps<{ label: string; name: string; maxMb?: number; max?: number; required?: boolean; help?: string; error?: string }>(), { maxMb: 5, max: 5 });
const model = defineModel<File[]>({ default: () => [] });
const { t } = useI18n();
const local = ref('');
const input = ref<HTMLInputElement>();
const TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

function pick(e: Event) {
    const files = [...((e.target as HTMLInputElement).files ?? [])];
    local.value = '';
    const ok: File[] = [];
    for (const f of files) {
        if (!TYPES.includes(f.type)) local.value = t('inner.form.fileType', { name: f.name });
        else if (f.size > props.maxMb * 1048576) local.value = t('inner.form.fileTooBig', { name: f.name, mb: props.maxMb });
        else ok.push(f);
    }
    const next = [...model.value, ...ok];
    if (next.length > props.max) local.value = t('inner.form.fileMax', { max: props.max });
    model.value = next.slice(0, props.max);
    if (input.value) input.value.value = '';
}
const remove = (i: number) => (model.value = model.value.filter((_, j) => j !== i));
const kb = (b: number) => (b > 1048576 ? `${(b / 1048576).toFixed(1)} MB` : `${Math.ceil(b / 1024)} KB`);
</script>

<template>
    <Field v-slot="{ id, describedby, invalid }" :label="label" :name="name" :required="required" :help="help ?? t('inner.form.fileHelp', { mb: maxMb, max })" :error="error ?? (local || undefined)">
        <label :for="id" class="flex min-h-24 cursor-pointer flex-col items-center justify-center gap-1 rounded-md border border-dashed bg-snow px-4 py-5 text-center transition-colors hover:border-green focus-within:border-green focus-within:ring-3 focus-within:ring-green/15" :class="invalid || local ? 'border-red' : 'border-night/25'">
            <Icon name="up" :size="20" class="text-green" />
            <span class="font-semibold text-green">{{ t('inner.form.files') }}</span>
            <input :id="id" ref="input" type="file" class="sr-only" multiple accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" :aria-describedby="describedby" :aria-invalid="invalid || !!local" @change="pick" />
        </label>
        <ul v-if="model.length" class="mt-3 space-y-2">
            <li v-for="(f, i) in model" :key="f.name + i" class="flex items-center gap-3 rounded-md border border-night/10 bg-snow px-3.5 py-2">
                <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ f.name }}</span>
                <span class="t-label text-ink-2">{{ kb(f.size) }}</span>
                <button type="button" class="grid min-h-11 min-w-11 place-items-center text-ink-2 hover:text-red-ink" :aria-label="t('inner.form.removeFile', { name: f.name })" @click="remove(i)"><Icon name="close" :size="16" /></button>
            </li>
        </ul>
    </Field>
</template>
