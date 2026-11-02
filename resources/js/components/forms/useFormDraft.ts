import type { InertiaForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';

/**
 * Keeps a multi-step form's text fields in sessionStorage (never files) so a refresh does not lose work.
 * `save()` is the explicit "Save as draft": it also keeps a copy in localStorage, restored on a later visit.
 */
export function useFormDraft<T extends object>(key: string, form: InertiaForm<T>, skip: string[] = []) {
    const storageKey = `fsb_draft_${key}`;
    const plain = () => Object.fromEntries(Object.entries(form.data() as Record<string, unknown>).filter(([k, v]) => !skip.includes(k) && !(v instanceof File) && !(Array.isArray(v) && v[0] instanceof File)));

    onMounted(() => {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey) ?? localStorage.getItem(storageKey) ?? 'null');
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
        /** True when the draft was stored on this device. */
        save: () => {
            try {
                localStorage.setItem(storageKey, JSON.stringify(plain()));
                return true;
            } catch {
                return false;
            }
        },
        clear: () => {
            try {
                sessionStorage.removeItem(storageKey);
                localStorage.removeItem(storageKey);
            } catch {
                /* storage blocked */
            }
        },
    };
}
