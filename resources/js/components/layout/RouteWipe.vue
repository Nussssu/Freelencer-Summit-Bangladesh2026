<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap, reducedMotion } from '@/composables/motion';

// Route-transition wipe driven by Inertia's navigation events (page visits only, not form posts or partial reloads).
const panel = ref<HTMLElement>();
const offs: (() => void)[] = [];
let active = false;

onMounted(() => {
    offs.push(
        router.on('start', (e) => {
            const v = e.detail.visit;
            if (reducedMotion() || v.method !== 'get' || v.only.length || v.url.pathname === location.pathname) return;
            active = true;
            gsap.fromTo(panel.value!, { scaleY: 0, transformOrigin: '50% 100%' }, { scaleY: 1, duration: 0.45, ease: 'expo.inOut' });
        }),
        router.on('finish', () => {
            if (!active) return;
            active = false;
            gsap.to(panel.value!, { scaleY: 0, transformOrigin: '50% 0%', duration: 0.45, ease: 'expo.inOut', delay: 0.05 });
        }),
    );
});
onBeforeUnmount(() => offs.forEach((off) => off()));
</script>

<template>
    <div ref="panel" class="pointer-events-none fixed inset-0 z-[85] origin-bottom scale-y-0 bg-delta" aria-hidden="true">
        <div class="stitch absolute inset-x-0 top-1/2" />
    </div>
</template>
