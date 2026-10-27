<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useShared } from '@/composables/useShared';

const { locale: active } = useShared();
const { locale, t } = useI18n();

function choose(l: 'en' | 'bn') {
    if (l === active.value) return;
    locale.value = l; // instant switch; the POST persists it in the session for SSR
    document.documentElement.lang = l;
    router.post('/locale', { locale: l }, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <div role="group" :aria-label="t('header.language')" class="flex items-center">
        <button type="button" class="t-label min-h-11 px-2 transition-colors duration-300 hover:text-gold" :class="active === 'en' ? 'text-gold' : 'opacity-70'" :aria-pressed="active === 'en'" lang="en" @click="choose('en')">EN</button>
        <span aria-hidden="true" class="opacity-40">|</span>
        <button type="button" class="min-h-11 px-2 font-bangla text-sm transition-colors duration-300 hover:text-gold" :class="active === 'bn' ? 'text-gold' : 'opacity-70'" :aria-pressed="active === 'bn'" lang="bn" @click="choose('bn')">বাং</button>
    </div>
</template>
