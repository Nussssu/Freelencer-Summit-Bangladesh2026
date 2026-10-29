<script setup lang="ts">
import SplitHeading from '@/components/ui/SplitHeading.vue';
import type { SectionHeading } from '@/types/inner';

// About-only section opener: a short stitched label (sentence case, not a tracked-out eyebrow) over a display heading.
withDefaults(defineProps<{ heading: SectionHeading; id?: string; size?: 'md' | 'lg' }>(), { size: 'md' });
</script>

<template>
    <div>
        <p v-if="heading.eyebrow" class="flex items-center gap-3 text-sm font-semibold text-green" data-reveal="fade">
            <span class="stitch w-10 shrink-0" aria-hidden="true" />{{ heading.eyebrow }}
        </p>
        <SplitHeading
            :id="id"
            :text="heading.title"
            :emphasis="heading.emphasis ?? undefined"
            emphasis-class="text-green"
            class="mt-4 [text-wrap:balance]"
            :class="size === 'lg' ? 't-h2-xl' : 't-h2'"
        />
        <p v-if="heading.intro" class="mt-6 max-w-[58ch] text-lg leading-relaxed text-ink-2" data-reveal="fade">{{ heading.intro }}</p>
    </div>
</template>
