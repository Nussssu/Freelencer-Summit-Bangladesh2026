import Lenis from 'lenis';
import { ScrollTrigger, gsap, reducedMotion } from './motion';

// One Lenis instance for the persistent layout, driven by the GSAP ticker so ScrollTrigger stays in sync.
let lenis: Lenis | null = null;
const tick = (time: number) => lenis?.raf(time * 1000);

export function startLenis() {
    if (lenis || reducedMotion()) return;
    lenis = new Lenis({ lerp: 0.085, wheelMultiplier: 1 }); // default: no smooth scroll measured on references
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add(tick);
    gsap.ticker.lagSmoothing(0);
}

export function destroyLenis() {
    gsap.ticker.remove(tick);
    lenis?.destroy();
    lenis = null;
}

export const pauseScroll = () => lenis?.stop();
export const resumeScroll = () => lenis?.start();
export function scrollToTarget(target: number | string | HTMLElement, immediate = false) {
    if (lenis) lenis.scrollTo(target, { immediate, offset: typeof target === 'number' ? 0 : -96 });
    else if (typeof target === 'number') window.scrollTo({ top: target, behavior: immediate || reducedMotion() ? 'auto' : 'smooth' });
    else (typeof target === 'string' ? document.querySelector(target) : target)?.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth' });
}
