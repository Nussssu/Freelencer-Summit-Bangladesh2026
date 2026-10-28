import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { scrollToTarget } from '@/composables/useLenis';

/**
 * Directory state synced to the URL: filters + page become the query string, and each change is an
 * Inertia partial reload of `only` (the list props). Text keys are debounced; chips and selects apply at once.
 * Initial values come from the server's echoed `filters` prop, so a shared URL restores the same view.
 */
export function useDirectory<T extends Record<string, string>>(initial: T, opts: { only: string[]; debounce?: (keyof T)[]; anchor?: string }) {
    const filters = reactive({ ...initial }) as T;
    const loading = ref(false);
    const filtered = computed(() => Object.values(filters).some(Boolean));
    const typed = opts.debounce ?? ['q'];
    let timer: ReturnType<typeof setTimeout> | undefined;
    let slow: ReturnType<typeof setTimeout> | undefined;

    function visit(page = 1) {
        const query = Object.fromEntries(Object.entries({ ...filters, page: page > 1 ? String(page) : '' }).filter(([, v]) => v));
        router.get(location.pathname, query, {
            only: opts.only,
            preserveState: true,
            preserveScroll: true,
            replace: true,
            // Skeletons only when the reload is slow enough to notice.
            onStart: () => (slow = setTimeout(() => (loading.value = true), 150)),
            onFinish: () => (clearTimeout(slow), (loading.value = false)),
        });
    }

    watch(
        () => ({ ...filters }),
        (now, before) => {
            clearTimeout(timer);
            if (typed.some((k) => now[k] !== before[k])) timer = setTimeout(() => visit(1), 300);
            else visit(1);
        },
    );
    onBeforeUnmount(() => (clearTimeout(timer), clearTimeout(slow)));

    function go(page: number) {
        visit(page);
        if (opts.anchor) scrollToTarget(opts.anchor);
    }
    function clear() {
        (Object.keys(filters) as (keyof T)[]).forEach((k) => ((filters[k] as string) = ''));
    }

    return { filters, loading, filtered, go, clear };
}
