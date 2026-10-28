<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useShared } from '@/composables/useShared';

const props = defineProps<{ title: string; description: string; path: string; image?: string; robots?: string }>(); // robots: e.g. 'noindex' on thank-you pages
const { site } = useShared();
const url = computed(() => site.value.url + props.path);
const image = computed(() => site.value.url + (props.image ?? '/og-image.png'));
</script>

<template>
    <Head :title="title">
        <meta head-key="description" name="description" :content="description" />
        <link head-key="canonical" rel="canonical" :href="url" />
        <meta v-if="robots" head-key="robots" name="robots" :content="robots" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:site_name" property="og:site_name" content="Freelancer Summit Bangladesh" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta head-key="og:description" property="og:description" :content="description" />
        <meta head-key="og:url" property="og:url" :content="url" />
        <meta head-key="og:image" property="og:image" :content="image" />
        <meta head-key="og:image:width" property="og:image:width" content="1200" />
        <meta head-key="og:image:height" property="og:image:height" content="630" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta head-key="twitter:description" name="twitter:description" :content="description" />
        <meta head-key="twitter:image" name="twitter:image" :content="image" />
    </Head>
</template>
