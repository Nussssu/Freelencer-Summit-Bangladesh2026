<script setup lang="ts">
import { computed } from 'vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import HeroSection from '@/components/sections/HeroSection.vue';
import JourneySection from '@/components/sections/JourneySection.vue';
import { useShared } from '@/composables/useShared';
import type { HomeContent } from '@/types/content';

// Exactly the client's 17 homepage sections, in brief order (17 = footer in the persistent layout).
defineProps<{ content: HomeContent }>();
const { summit } = useShared();
const title = computed(() => `Freelancer Summit Bangladesh ${summit.value.edition} · Grand Summit, Dhaka`);
const description = computed(
    () => `The national flagship summit for Bangladeshi freelancers: ${summit.value.campuses} campus programmes, ${summit.value.districts} district roadshows and ${summit.value.divisions} divisional summits leading to the Grand Summit in Dhaka, late November ${summit.value.edition}.`,
);
</script>

<template>
    <SeoHead :title="title" :description="description" path="/" />
    <HeroSection :hero="content.hero" />
    <JourneySection :journey="content.journey" />
</template>
