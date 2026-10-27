<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, onBeforeUnmount, onMounted, ref, watch } from 'vue';
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
import { STAGE_OFF_CLASS, stageMode } from '@/stage/state';
import type { SharedProps } from '@/types/content';

// Persistent layout: survives Inertia navigations, so header, footer, smooth scroll, cursor and the 3D stage stay alive.
const DeltaStage = defineAsyncComponent(() => import('@/stage/DeltaStage.vue'));

const page = usePage<SharedProps>();
const { locale, t } = useI18n();
const stageOn = ref(false);
const cursorOn = ref(false);
const preloading = ref(false);
const summit = computed(() => page.props.summit);

watch(
    () => page.props.locale,
    (l) => {
        locale.value = l;
        if (typeof document !== 'undefined') document.documentElement.lang = l;
    },
);

function canRunStage() {
    if (new URLSearchParams(location.search).has('stage')) return true; // QA / capture override
    const nav = navigator as Navigator & { connection?: { saveData?: boolean }; deviceMemory?: number };
    if (innerWidth < 768 || reducedMotion() || nav.connection?.saveData || (nav.hardwareConcurrency ?? 8) <= 2 || (nav.deviceMemory ?? 8) < 2) return false;
    try {
        const c = document.createElement('canvas');
        return !!(c.getContext('webgl2') || c.getContext('webgl'));
    } catch {
        return false;
    }
}

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

    if (canRunStage()) {
        // Load three.js only after first paint.
        const go = () => (stageOn.value = true);
        'requestIdleCallback' in window ? requestIdleCallback(go, { timeout: 1500 }) : setTimeout(go, 600);
    } else {
        document.documentElement.classList.add(STAGE_OFF_CLASS);
        stageMode.value = 'off';
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
    <DeltaStage v-if="stageOn" :districts="summit.districts" :beacon-label="`Nov ${summit.edition}`" />
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
