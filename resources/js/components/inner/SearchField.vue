<script setup lang="ts">
import { useId } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';

// Directory search with a visible label; debouncing lives in useDirectory.
defineProps<{ label?: string; placeholder?: string }>();
const model = defineModel<string>({ default: '' });
const { t } = useI18n();
const id = useId();
</script>

<template>
    <div>
        <label :for="id" class="t-label text-ink-2">{{ label ?? t('inner.list.search') }}</label>
        <div class="relative mt-1.5">
            <Icon name="search" :size="18" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-2" />
            <input
                :id="id"
                v-model="model"
                type="search"
                :placeholder="placeholder"
                autocomplete="off"
                class="block min-h-11 w-full rounded-md border border-night/15 bg-snow py-2.5 pr-3.5 pl-11 text-night transition-[border-color,box-shadow] duration-200 outline-none hover:border-night/30 focus:border-green focus:ring-3 focus:ring-green/15"
            />
        </div>
    </div>
</template>
