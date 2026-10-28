<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import type { Crumb } from '@/types/inner';

// Home → … → current page (last crumb has no href). JSON-LD for the same trail is built in PHP (App\Support\Seo).
defineProps<{ items: Crumb[] }>();
const { t } = useI18n();
</script>

<template>
    <nav :aria-label="t('inner.crumbs.label')">
        <ol class="t-label flex flex-wrap items-center gap-x-2 gap-y-1">
            <li><Link href="/" class="link-u">{{ t('inner.crumbs.home') }}</Link></li>
            <li v-for="(c, i) in items" :key="c.label" class="flex items-center gap-2">
                <span aria-hidden="true" class="opacity-50">/</span>
                <Link v-if="c.href && i < items.length - 1" :href="c.href" class="link-u">{{ c.label }}</Link>
                <span v-else aria-current="page" class="opacity-75">{{ c.label }}</span>
            </li>
        </ol>
    </nav>
</template>
