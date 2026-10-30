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
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-ink-2">
            <li><Link href="/" class="transition-colors duration-300 hover:text-green">{{ t('inner.crumbs.home') }}</Link></li>
            <li v-for="(c, i) in items" :key="c.label" class="flex items-center gap-2">
                <span aria-hidden="true" class="opacity-50">/</span>
                <Link v-if="c.href && i < items.length - 1" :href="c.href" class="transition-colors duration-300 hover:text-green">{{ c.label }}</Link>
                <span v-else aria-current="page" class="text-night">{{ c.label }}</span>
            </li>
        </ol>
    </nav>
</template>
