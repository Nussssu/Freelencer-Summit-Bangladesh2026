<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

// 2px progress bar; writes transform directly (no reactive per-frame state).
const bar = ref<HTMLElement>();
let raf = 0;
const update = () => {
    raf = 0;
    const max = document.documentElement.scrollHeight - innerHeight;
    if (bar.value) bar.value.style.transform = `scaleX(${max > 0 ? scrollY / max : 0})`;
};
const onScroll = () => (raf ||= requestAnimationFrame(update));
onMounted(() => (addEventListener('scroll', onScroll, { passive: true }), update()));
onBeforeUnmount(() => (removeEventListener('scroll', onScroll), cancelAnimationFrame(raf)));
</script>

<template>
    <div ref="bar" class="fixed inset-x-0 top-0 z-[70] h-0.5 origin-left scale-x-0 bg-gold" aria-hidden="true" />
</template>
