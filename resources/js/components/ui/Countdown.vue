<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { pad, useCountdown } from '@/composables/useCountdown';
import { gsap, reducedMotion } from '@/composables/motion';

// Hero countdown under the CTAs (BPO B4 placement, GITEX G7 scale). Digits roll on change; placeholders render on the server.
const props = withDefaults(defineProps<{ target: string; compact?: boolean }>(), { compact: false });
const { t } = useI18n();
const { parts } = useCountdown(props.target);

const units = computed(() => {
    const p = parts.value;
    return (['days', 'hours', 'minutes', 'seconds'] as const).map((k) => ({ k, v: p ? pad(p[k]) : '--' }));
});

const root = ref<HTMLElement>();
let stop = () => {};
onMounted(() => {
    if (props.compact || reducedMotion()) return;
    stop = watch(
        () => units.value.map((u) => u.v).join(),
        (now, before) => {
            if (!before || before.includes('-')) return;
            const prev = before.split(',');
            now.split(',').forEach((v, i) => {
                if (v !== prev[i]) gsap.fromTo(root.value!.querySelectorAll('[data-unit]')[i], { yPercent: -40, opacity: 0 }, { yPercent: 0, opacity: 1, duration: 0.45, ease: 'expo.out' });
            });
        },
        { flush: 'post' },
    );
});
onBeforeUnmount(() => stop());
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
