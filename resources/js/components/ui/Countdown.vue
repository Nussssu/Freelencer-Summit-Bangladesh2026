<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { pad, useCountdown } from '@/composables/useCountdown';
import { ScrollTrigger, gsap, reducedMotion } from '@/composables/motion';

// Hero countdown under the CTAs (BPO B4 placement, GITEX G7 scale). Spins up from 0 when it scrolls into view, then digits roll on change; placeholders render on the server.
const props = withDefaults(defineProps<{ target: string; compact?: boolean }>(), { compact: false });
const { t } = useI18n();
const { parts } = useCountdown(props.target);

// Count-up progress 0→1 while the intro spin runs; null once it settles on the live values.
const spin = ref<number | null>(null);
const units = computed(() => {
    const p = parts.value;
    const f = spin.value;
    return (['days', 'hours', 'minutes', 'seconds'] as const).map((k) => ({ k, v: p ? pad(f === null ? p[k] : Math.round(p[k] * f)) : '--' }));
});

const root = ref<HTMLElement>();
let stop = () => {};
let intro: gsap.core.Tween | undefined;
let trigger: ScrollTrigger | undefined;
let plays = 0;
const play = () => {
    const s = { f: 0 };
    intro?.kill();
    spin.value = 0;
    // First run waits for the hero fade-in; replays on scroll-back start at once.
    intro = gsap.to(s, { f: 1, duration: 1.8, ease: 'expo.out', delay: plays++ ? 0 : 0.6, onUpdate: () => (spin.value = s.f), onComplete: () => (spin.value = null) });
};
onMounted(() => {
    if (props.compact || reducedMotion()) return;
    trigger = ScrollTrigger.create({ trigger: root.value, start: 'top 90%', end: 'bottom top', onEnter: play, onEnterBack: play });
    stop = watch(
        () => units.value.map((u) => u.v).join(),
        (now, before) => {
            if (spin.value !== null || !before || before.includes('-')) return;
            const prev = before.split(',');
            now.split(',').forEach((v, i) => {
                if (v !== prev[i]) gsap.fromTo(root.value!.querySelectorAll('[data-unit]')[i], { yPercent: -40, opacity: 0 }, { yPercent: 0, opacity: 1, duration: 0.45, ease: 'expo.out' });
            });
        },
        { flush: 'post' },
    );
});
onBeforeUnmount(() => {
    stop();
    trigger?.kill();
    intro?.kill();
});
</script>

<template>
    <div ref="root" role="timer" :aria-label="t('countdown.label')">
        <p v-if="compact" class="t-label tabular-nums" aria-hidden="true">
            {{ units[0].v }}d : {{ units[1].v }}h : {{ units[2].v }}m <span class="opacity-60">{{ t('countdown.short') }}</span>
        </p>
        <dl v-else class="grid grid-cols-4 border-t border-line-dark">
            <div v-for="u in units" :key="u.k" class="flex flex-col-reverse justify-end border-r border-line-dark py-3 pr-2 last:border-r-0 [&:not(:first-child)]:pl-3 md:[&:not(:first-child)]:pl-4">
                <dt class="t-label mt-2 text-muted">{{ t(`countdown.${u.k}`) }}</dt>
                <dd data-unit class="t-stat" style="font-size: clamp(2rem, 4vw, 3.1rem)">{{ u.v }}</dd>
            </div>
        </dl>
    </div>
</template>
