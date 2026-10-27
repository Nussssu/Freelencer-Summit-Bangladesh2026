<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap } from '@/composables/motion';

// Fine pointers only (mounted conditionally by the layout). A ring that trails the pointer and grows over links.
const ring = ref<HTMLElement>();
let off = () => {};
onMounted(() => {
    const el = ring.value!;
    const x = gsap.quickTo(el, 'x', { duration: 0.35, ease: 'power3.out' });
    const y = gsap.quickTo(el, 'y', { duration: 0.35, ease: 'power3.out' });
    const move = (e: PointerEvent) => {
        x(e.clientX);
        y(e.clientY);
        el.dataset.hot = (e.target as Element).closest('a,button,[role="tab"]') ? '1' : '';
    };
    addEventListener('pointermove', move, { passive: true });
    off = () => removeEventListener('pointermove', move);
});
onBeforeUnmount(() => off());
</script>

<template>
    <div ref="ring" class="cursor-ring pointer-events-none fixed top-0 left-0 z-[80] hidden md:block" aria-hidden="true" />
</template>

<style scoped>
.cursor-ring {
    width: 28px;
    height: 28px;
    margin: -14px 0 0 -14px;
    border: 1px solid var(--gold);
    mix-blend-mode: difference;
    transition:
        width 0.3s ease-in-out,
        height 0.3s ease-in-out,
        margin 0.3s ease-in-out;
}
.cursor-ring[data-hot='1'] {
    width: 56px;
    height: 56px;
    margin: -28px 0 0 -28px;
}
</style>
