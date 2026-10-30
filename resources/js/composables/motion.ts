import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { onBeforeUnmount, onMounted, type Ref } from 'vue';

export const isClient = typeof window !== 'undefined';
if (isClient) gsap.registerPlugin(ScrollTrigger);

export { gsap, ScrollTrigger };

export const EASE = 'expo.out';
export const STAGGER = 0.055;

/** Resolves once the first-visit preloader starts opening (or straight away when there is none), so the hero intro isn't spent behind it. */
let releaseIntro = () => {};
export const introReady = isClient ? new Promise<void>((r) => (releaseIntro = r)) : Promise.resolve();
export const startIntro = () => releaseIntro();

export const reducedMotion = () => isClient && matchMedia('(prefers-reduced-motion: reduce)').matches;
export const finePointer = () => isClient && matchMedia('(hover: hover) and (pointer: fine)').matches;

/**
 * Play-once reveals should animate only when the visitor scrolls to them. On reload Inertia restores the old scroll
 * position, which fires every trigger it jumps past (including the one on screen) without anyone scrolling, so until
 * the visitor's first wheel / touch / key / pointer input a reveal snaps to its end state instead of playing.
 */
let userScrolled = false;
if (isClient) {
    const mark = () => {
        userScrolled = true;
        ['wheel', 'touchstart', 'keydown', 'pointerdown'].forEach((e) => removeEventListener(e, mark));
    };
    ['wheel', 'touchstart', 'keydown', 'pointerdown'].forEach((e) => addEventListener(e, mark, { passive: true }));
}
export function reveal(play: () => void) {
    if (userScrolled) return play();
    gsap.context(play).getTweens().forEach((t: gsap.core.Animation) => t.progress(1));
}

/** GSAP work scoped to a component: created after mount, reverted before unmount. */
export function useGsap(scope: Ref<HTMLElement | null | undefined>, setup: (reduced: boolean) => void) {
    let ctx: gsap.Context | undefined;
    onMounted(() => {
        ctx = gsap.context(() => setup(reducedMotion()), scope.value ?? undefined);
    });
    onBeforeUnmount(() => ctx?.revert());
}
