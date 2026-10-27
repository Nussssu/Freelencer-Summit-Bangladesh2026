import { nextTick, onBeforeUnmount, watch, type Ref } from 'vue';

const FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select,textarea,[tabindex]:not([tabindex="-1"])';

/** Traps Tab inside `el` while `open`, closes on Escape, restores focus to the opener. */
export function useFocusTrap(el: Ref<HTMLElement | null | undefined>, open: Ref<boolean>, close: () => void) {
    let opener: HTMLElement | null = null;
    const onKey = (e: KeyboardEvent) => {
        if (e.key === 'Escape') return close();
        if (e.key !== 'Tab' || !el.value) return;
        const items = [...el.value.querySelectorAll<HTMLElement>(FOCUSABLE)].filter((n) => n.offsetParent !== null);
        if (!items.length) return;
        const [first, last] = [items[0], items[items.length - 1]];
        if (e.shiftKey && document.activeElement === first) (e.preventDefault(), last.focus());
        else if (!e.shiftKey && document.activeElement === last) (e.preventDefault(), first.focus());
    };
    watch(open, async (isOpen) => {
        if (isOpen) {
            opener = document.activeElement as HTMLElement;
            document.addEventListener('keydown', onKey);
            await nextTick();
            el.value?.querySelector<HTMLElement>('[data-autofocus]')?.focus() ?? el.value?.querySelector<HTMLElement>(FOCUSABLE)?.focus();
        } else {
            document.removeEventListener('keydown', onKey);
            opener?.focus();
        }
    });
    onBeforeUnmount(() => document.removeEventListener('keydown', onKey));
}
