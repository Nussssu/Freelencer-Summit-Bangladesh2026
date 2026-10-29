<script setup lang="ts">
import SplitHeading from '@/components/ui/SplitHeading.vue';
import type { SectionHeading } from '@/types/inner';

// Inner-page section opener, same pattern as the landing: teal label, display heading, optional one-line intro.
defineProps<{ heading: SectionHeading; id?: string }>();
</script>

<template>
    <div>
        <p v-if="heading.eyebrow" class="t-label text-green" data-reveal="fade">{{ heading.eyebrow }}</p>
        <SplitHeading :id="id" :text="heading.title" :emphasis="heading.emphasis ?? undefined" emphasis-class="text-green" class="t-h2 mt-3" />
        <p v-if="heading.intro" class="mt-5 max-w-2xl text-lg text-ink-2" data-reveal="fade">{{ heading.intro }}</p>
    </div>
</template>
