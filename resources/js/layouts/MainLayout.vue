<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import CustomCursor from '@/components/layout/CustomCursor.vue';
import MobileCtaBar from '@/components/layout/MobileCtaBar.vue';
import Preloader from '@/components/layout/Preloader.vue';
import RouteWipe from '@/components/layout/RouteWipe.vue';
import ScrollProgress from '@/components/layout/ScrollProgress.vue';
import SiteFooter from '@/components/layout/SiteFooter.vue';
import SiteHeader from '@/components/layout/SiteHeader.vue';
import { ScrollTrigger, finePointer, reducedMotion } from '@/composables/motion';
import { destroyLenis, scrollToTarget, startLenis } from '@/composables/useLenis';
import { captureUtm } from '@/composables/useUtm';
import type { SharedProps } from '@/types/content';

const page = usePage<SharedProps>();
const { locale, t } = useI18n();
const cursorOn = ref(false);
const preloading = ref(false);

watch(
    () => page.props.locale,
    (l) => {
        locale.value = l;
        if (typeof document !== 'undefined') document.documentElement.lang = l;
    },
);

let offs: (() => void)[] = [];
onMounted(() => {
    captureUtm();
    startLenis();
    cursorOn.value = finePointer() && !reducedMotion();

    try {
        preloading.value = page.component === 'Home' && !reducedMotion() && !sessionStorage.getItem('fsb_pre');
        sessionStorage.setItem('fsb_pre', '1');
    } catch {
        preloading.value = false;
    }

    offs = [
        router.on('navigate', () => {
            if (!location.hash) scrollToTarget(0, true);
            requestAnimationFrame(() => ScrollTrigger.refresh());
        }),
    ];
});
onBeforeUnmount(() => {
    offs.forEach((off) => off());
    destroyLenis();
});
</script>

<template>
    <a href="#main" class="sr-only-focusable fixed top-2 left-2 z-[100] bg-gold px-4 py-3 text-night">{{ t('header.skip') }}</a>
    <ScrollProgress />
    <SiteHeader />
    <main id="main" tabindex="-1" class="relative z-[1]">
        <slot />
    </main>
    <SiteFooter />
    <MobileCtaBar />
    <CustomCursor v-if="cursorOn" />
    <RouteWipe />
    <Preloader v-if="preloading" @done="preloading = false" />
</template>
