<script setup lang="ts">
import type { Nominee } from '@/types/inner';

// Shortlisted nominee: portrait with a Shortlisted badge, name, role and the award category as plain text.
// `category: false` drops the category line where a heading already names it (the nominees page).
withDefaults(defineProps<{ nominee: Nominee; category?: boolean }>(), { category: true });
</script>

<template>
    <article class="in-card is-solid h-full overflow-hidden">
        <div class="@container relative aspect-[355/222] overflow-hidden">
            <img :src="nominee.photo" alt="" loading="lazy" class="h-full w-full object-cover" />
            <!-- Sized in step with the photo so it sits exactly over the mock-up's own badge -->
            <span class="absolute top-[4.3%] right-[2.3%] flex h-[18.5%] w-[41%] items-center justify-center gap-[3%] rounded-full bg-white text-[4.6cqw] font-semibold text-night">
                <svg class="size-[1.15em] shrink-0 text-green" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="currentColor" /><path d="m7 12.5 3.2 3.2L17 9" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Shortlisted
            </span>
        </div>
        <div class="flex flex-1 flex-col items-start p-6">
            <h3 class="text-2xl font-bold text-night">{{ nominee.name }}</h3>
            <p class="mt-1 text-lg text-ink-2">{{ nominee.role }}</p>
            <p v-if="category" class="mt-3 font-medium text-green">{{ nominee.category }}</p>
        </div>
    </article>
</template>
