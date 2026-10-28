import type { InertiaForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';

/** Keeps a multi-step form's text fields in sessionStorage (never files) so a refresh does not lose work. */
export function useFormDraft<T extends object>(key: string, form: InertiaForm<T>, skip: string[] = []) {
    const storageKey = `fsb_draft_${key}`;
    const plain = () => Object.fromEntries(Object.entries(form.data() as Record<string, unknown>).filter(([k, v]) => !skip.includes(k) && !(v instanceof File) && !(Array.isArray(v) && v[0] instanceof File)));

    onMounted(() => {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey) ?? 'null');
            if (saved) Object.assign(form, saved);
        } catch {
            /* storage blocked or corrupt: start empty */
        }
    });
    watch(
        () => plain(),
        (v) => {
            try {
                sessionStorage.setItem(storageKey, JSON.stringify(v));
            } catch {
                /* storage blocked */
            }
        },
        { deep: true },
    );

    return {
        clear: () => {
            try {
                sessionStorage.removeItem(storageKey);
            } catch {
                /* storage blocked */
            }
        },
    };
}
