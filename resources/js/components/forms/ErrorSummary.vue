<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

// Lists server errors (FormRequest attributes give them human field names) with links to each field; takes focus when errors arrive (WCAG 3.3.1).
const props = defineProps<{ errors: Record<string, string> }>();
const { t } = useI18n();
const box = ref<HTMLElement>();
watch(
    () => Object.keys(props.errors).length,
    async (n) => n && (await nextTick(), box.value?.focus()),
);
function jump(key: string) {
    const field = document.querySelector<HTMLElement>(`[data-field="${key.split('.')[0]}"]`);
    field?.querySelector<HTMLElement>('input, select, textarea')?.focus();
    field?.scrollIntoView({ block: 'center' });
}
</script>

<template>
    <div v-if="Object.keys(errors).length" ref="box" tabindex="-1" role="alert" class="rounded-md border border-red bg-red/5 p-5 outline-none">
        <p class="font-semibold text-red-ink">{{ t('inner.form.errorTitle', Object.keys(errors).length) }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li v-for="(msg, key) in errors" :key="key">
                <a :href="`#${key}`" class="underline underline-offset-2" @click.prevent="jump(String(key))">{{ msg }}</a>
            </li>
        </ul>
    </div>
</template>
