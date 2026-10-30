<script setup lang="ts">
import { useId } from 'vue';
import type { Consent } from '@/types/inner';

// Consent checkbox with the versioned wording from config/consent.php (the server stores the version).
defineProps<{ consent: Consent; error?: string }>();
const model = defineModel<boolean>({ default: false });
const id = useId();
</script>

<template>
    <div data-field="consent">
        <label :for="id" class="flex cursor-pointer gap-3 rounded-xl border bg-snow p-4" :class="error ? 'border-red' : 'border-night/15'">
            <input :id="id" v-model="model" type="checkbox" name="consent" required :aria-invalid="!!error" :aria-describedby="error ? `${id}-err` : undefined" class="mt-1 size-5 shrink-0 accent-[var(--green)]" />
            <span>{{ consent.text }} <span class="text-red-ink" aria-hidden="true">*</span></span>
        </label>
        <p v-if="error" :id="`${id}-err`" class="mt-1.5 text-sm font-medium text-red-ink">{{ error }}</p>
    </div>
</template>
