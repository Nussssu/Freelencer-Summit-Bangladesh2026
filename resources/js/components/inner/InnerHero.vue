<script setup lang="ts">
import type { InnerPage } from '@/types/inner';
import InBtn from './InBtn.vue';
import PageHero from './PageHero.vue';

// PageHero fed straight from the controller's `page` (title, emphasis, intro, image, actions, breadcrumbs).
withDefaults(defineProps<{ page: InnerPage; variant?: 'text' | 'photo' | 'facts'; imagePosition?: string }>(), { variant: 'text', imagePosition: 'center' });
</script>

<template>
    <PageHero :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" :image="page.hero.image ?? null" :image-position="imagePosition" :crumbs="page.crumbs" :variant="variant">
        <template v-if="page.hero.actions?.length || $slots.actions" #actions>
            <InBtn v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</InBtn>
            <slot name="actions" />
        </template>
        <template v-if="$slots.aside" #aside><slot name="aside" /></template>
        <template v-if="$slots.below" #below><slot name="below" /></template>
    </PageHero>
</template>
