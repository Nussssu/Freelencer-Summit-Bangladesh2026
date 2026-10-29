<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ScrollTrigger, gsap, reducedMotion } from '@/composables/motion';

// Odometer-style count-up (GITEX G7): each digit is a 0–9 ribbon that rolls into place once in view.
// `spins` adds that many full 0–9 turns per digit before it lands; `scrub` ties the roll to the scroll instead of playing once.
const props = withDefaults(defineProps<{ value: number; suffix?: string; duration?: number; spins?: number; scrub?: boolean }>(), { suffix: '', duration: 2.2, spins: 0, scrub: false });
const cells = (props.spins + 1) * 10;

const digits = props.value.toLocaleString('en-US').split('');
const root = ref<HTMLElement>();

onMounted(() => {
    if (reducedMotion() || !root.value) return;
    const ribbons = root.value.querySelectorAll<HTMLElement>('[data-ribbon]');
    // Clear the server-rendered resting offset too, or GSAP keeps it as a pixel `y` on top of the yPercent roll.
    gsap.set(ribbons, { y: 0, yPercent: 0 });
    const land = (r: HTMLElement) => (-100 / cells) * (props.spins * 10 + Number(r.dataset.ribbon));
    if (props.scrub) {
        // refreshPriority -1: measured after any pin above it (this mounts before the parent creates its pin).
        const tl = gsap.timeline({ scrollTrigger: { trigger: root.value, start: 'top 95%', end: 'top 35%', scrub: 0.6, refreshPriority: -1 } });
        ribbons.forEach((r, i) => tl.to(r, { yPercent: land(r), duration: 1, ease: 'power2.out' }, i * 0.08));
        return;
    }
    ScrollTrigger.create({
        trigger: root.value,
        start: 'top 85%',
        once: true,
        refreshPriority: -1, // measured after any pin above it, so it fires when the number is actually on screen
        onEnter: () =>
            ribbons.forEach((r, i) =>
                gsap.to(r, { yPercent: land(r), duration: props.duration, ease: 'expo.out', delay: i * 0.08 }),
            ),
    });
});
</script>

<template>
    <span ref="root" class="inline-flex items-baseline" :aria-label="`${value.toLocaleString('en-US')}${suffix}`" role="text">
        <template v-for="(d, i) in digits" :key="i">
            <span v-if="/\d/.test(d)" aria-hidden="true" class="relative inline-block h-[1em] overflow-hidden leading-none">
                <span class="invisible">0</span>
                <span :data-ribbon="d" class="absolute inset-x-0 top-0 flex flex-col" :style="{ transform: `translateY(${(-100 / cells) * (spins * 10 + Number(d))}%)` }">
                    <span v-for="n in cells" :key="n" class="block h-[1em] leading-none">{{ (n - 1) % 10 }}</span>
                </span>
            </span>
            <span v-else aria-hidden="true">{{ d }}</span>
        </template>
        <span aria-hidden="true" class="text-gold">{{ suffix }}</span>
    </span>
</template>
