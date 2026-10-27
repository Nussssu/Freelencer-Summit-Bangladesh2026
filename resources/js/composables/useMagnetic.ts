import { onBeforeUnmount, onMounted, type Ref } from 'vue';
import { finePointer, gsap, reducedMotion } from './motion';

/** Subtle pull toward the cursor (max 6px). Fine pointers only; used on the two biggest CTAs. */
export function useMagnetic(el: Ref<HTMLElement | null | undefined>, strength = 6) {
    let off = () => {};
    onMounted(() => {
        const node = el.value;
        if (!node || !finePointer() || reducedMotion()) return;
        const x = gsap.quickTo(node, 'x', { duration: 0.4, ease: 'power3.out' });
        const y = gsap.quickTo(node, 'y', { duration: 0.4, ease: 'power3.out' });
        const move = (e: PointerEvent) => {
            const r = node.getBoundingClientRect();
            x(((e.clientX - r.left) / r.width - 0.5) * 2 * strength);
            y(((e.clientY - r.top) / r.height - 0.5) * 2 * strength);
        };
        const leave = () => (x(0), y(0));
        node.addEventListener('pointermove', move);
        node.addEventListener('pointerleave', leave);
        off = () => (node.removeEventListener('pointermove', move), node.removeEventListener('pointerleave', leave));
    });
    onBeforeUnmount(() => off());
}
