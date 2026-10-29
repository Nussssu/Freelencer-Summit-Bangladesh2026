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

/** GSAP work scoped to a component: created after mount, reverted before unmount. */
export function useGsap(scope: Ref<HTMLElement | null | undefined>, setup: (reduced: boolean) => void) {
    let ctx: gsap.Context | undefined;
    onMounted(() => {
        ctx = gsap.context(() => setup(reducedMotion()), scope.value ?? undefined);
    });
    onBeforeUnmount(() => ctx?.revert());
}
