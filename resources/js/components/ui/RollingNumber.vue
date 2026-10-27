<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ScrollTrigger, gsap, reducedMotion } from '@/composables/motion';

// Odometer-style count-up (GITEX G7): each digit is a 0–9 ribbon that rolls into place once in view.
const props = withDefaults(defineProps<{ value: number; suffix?: string; duration?: number }>(), { suffix: '', duration: 2.2 });

const digits = props.value.toLocaleString('en-US').split('');
const root = ref<HTMLElement>();

onMounted(() => {
    if (reducedMotion() || !root.value) return;
    const ribbons = root.value.querySelectorAll<HTMLElement>('[data-ribbon]');
    gsap.set(ribbons, { yPercent: 0 });
    ScrollTrigger.create({
        trigger: root.value,
        start: 'top 90%',
        once: true,
        onEnter: () =>
            ribbons.forEach((r, i) =>
                gsap.to(r, { yPercent: -10 * Number(r.dataset.ribbon), duration: props.duration, ease: 'expo.out', delay: i * 0.08 }),
            ),
    });
});
</script>

<template>
    <span ref="root" class="inline-flex items-baseline" :aria-label="`${value.toLocaleString('en-US')}${suffix}`" role="text">
        <template v-for="(d, i) in digits" :key="i">
            <span v-if="/\d/.test(d)" aria-hidden="true" class="relative inline-block h-[1em] overflow-hidden leading-none">
                <span class="invisible">0</span>
                <span :data-ribbon="d" class="absolute inset-x-0 top-0 flex flex-col" :style="{ transform: `translateY(${-10 * Number(d)}%)` }">
                    <span v-for="n in 10" :key="n" class="block h-[1em] leading-none">{{ n - 1 }}</span>
                </span>
            </span>
            <span v-else aria-hidden="true">{{ d }}</span>
        </template>
        <span aria-hidden="true" class="text-gold">{{ suffix }}</span>
    </span>
</template>
