<script setup lang="ts">
import { ref } from 'vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';

// Calmer closing band than the landing's final CTA: soft teal ground, one heading, one sentence, the actions.
defineProps<{ title: string; emphasis?: string; text?: string }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="soft-green section on-light light-tokens">
        <div class="container-site grid items-end gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <SplitHeading :text="title" :emphasis="emphasis" emphasis-class="text-green" class="t-h2" />
                <div class="stitch mt-6 max-w-xs" data-reveal="stitch" aria-hidden="true" />
                <p v-if="text" class="mt-6 max-w-xl text-lg" data-reveal="fade">{{ text }}</p>
            </div>
            <div class="flex flex-wrap gap-3 lg:col-span-4 lg:justify-end" data-reveal="fade"><slot /></div>
        </div>
    </section>
</template>
