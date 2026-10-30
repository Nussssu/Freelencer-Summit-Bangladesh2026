<script setup lang="ts">
import { ref } from 'vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';

// Closing panel for form and detail pages: a rounded teal-washed card with one headline, one sentence and the actions.
defineProps<{ title: string; emphasis?: string; text?: string }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" class="in-section">
        <div class="container-site">
            <div class="in-card tone-teal is-washed relative overflow-hidden px-6 py-10 md:px-12 md:py-14">
                <span class="in-pool -top-24 -right-24 h-72 w-72" aria-hidden="true" />
                <div class="grid items-center gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <SplitHeading :text="title" :emphasis="emphasis" emphasis-class="text-green" class="in-h2" />
                        <p v-if="text" class="in-lead mt-5 max-w-xl" data-reveal="fade">{{ text }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3 lg:col-span-5 lg:justify-end" data-reveal="fade"><slot /></div>
                </div>
            </div>
        </div>
    </section>
</template>
