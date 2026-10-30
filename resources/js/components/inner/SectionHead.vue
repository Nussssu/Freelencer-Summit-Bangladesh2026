<script setup lang="ts">
import SplitHeading from '@/components/ui/SplitHeading.vue';
import type { SectionHeading } from '@/types/inner';

// Inner-page section opener, as on the landing: one uppercase Host Grotesk headline with the emphasis in teal, no eyebrow
// and no brief under it. The default slot sits opposite the heading (a "view all" link or an action).
withDefaults(defineProps<{ heading: Pick<SectionHeading, 'title' | 'emphasis'>; id?: string; center?: boolean }>(), { center: false });
</script>

<template>
    <div class="in-head" :class="center && 'justify-center! text-center'">
        <SplitHeading :id="id" :text="heading.title" :emphasis="heading.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 max-w-4xl" />
        <div v-if="$slots.default" class="flex flex-wrap gap-3" data-reveal="fade"><slot /></div>
    </div>
</template>
