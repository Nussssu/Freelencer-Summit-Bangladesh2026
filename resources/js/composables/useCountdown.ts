import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

export const pad = (n: number) => String(n).padStart(2, '0');

/** Client-only ticking: `parts` is null during SSR and the first client render, so hydration matches. */
export function useCountdown(target: string) {
    const now = ref<number | null>(null);
    let timer: ReturnType<typeof setInterval> | undefined;
    onMounted(() => {
        now.value = Date.now();
        timer = setInterval(() => (now.value = Date.now()), 1000);
    });
    onBeforeUnmount(() => clearInterval(timer));

    const parts = computed(() => {
        if (now.value === null) return null;
        const ms = Math.max(0, new Date(target).getTime() - now.value);
        const s = Math.floor(ms / 1000);
        return { days: Math.floor(s / 86400), hours: Math.floor(s / 3600) % 24, minutes: Math.floor(s / 60) % 60, seconds: s % 60, done: ms === 0 };
    });
    return { parts };
}
