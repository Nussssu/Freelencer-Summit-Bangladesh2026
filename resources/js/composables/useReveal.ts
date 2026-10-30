import { type Ref } from 'vue';
import { EASE, STAGGER, ScrollTrigger, gsap, reveal, useGsap } from './motion';

/**
 * One reveal system for every section. Inside `root`:
 *  [data-reveal="lines"]  words (from <SplitHeading>) rise inside masks, one stagger step per line
 *  [data-reveal="fade"]   fade + 24px rise
 *  [data-reveal="clip"]   clip-path wipe from the top (images, tiles)
 *  [data-reveal="stitch"] kantha stitch line sews in left to right
 * Each plays once. Initial hidden states only exist under html.js-motion, so SSR / no-JS stays visible.
 */
export function useReveal(root: Ref<HTMLElement | null | undefined>) {
    useGsap(root, (reduced) => {
        const el = root.value;
        if (!el || reduced) return;
        const once = (target: Element, play: () => void) =>
            ScrollTrigger.create({ trigger: target, start: 'top 88%', once: true, onEnter: () => reveal(play) });

        el.querySelectorAll<HTMLElement>('[data-reveal="lines"]').forEach((block) => {
            const words = [...block.querySelectorAll<HTMLElement>('.line-inner')];
            const tops = [...new Set(words.map((w) => w.parentElement!.offsetTop))];
            gsap.set(words, { yPercent: 105 });
            once(block, () =>
                gsap.to(words, { yPercent: 0, duration: 0.9, ease: EASE, delay: (i) => tops.indexOf(words[i].parentElement!.offsetTop) * STAGGER }),
            );
        });
        el.querySelectorAll<HTMLElement>('[data-reveal="fade"]').forEach((t, i) => {
            gsap.set(t, { opacity: 0, y: 24 });
            once(t, () => gsap.to(t, { opacity: 1, y: 0, duration: 0.9, ease: EASE, delay: (i % 4) * STAGGER }));
        });
        el.querySelectorAll<HTMLElement>('[data-reveal="clip"]').forEach((t, i) => {
            gsap.set(t, { clipPath: 'inset(0 0 100% 0)' });
            once(t, () => gsap.to(t, { clipPath: 'inset(0 0 0% 0)', duration: 1.1, ease: EASE, delay: (i % 6) * STAGGER }));
        });
        el.querySelectorAll<HTMLElement>('[data-reveal="stitch"]').forEach((t) => {
            gsap.set(t, { clipPath: 'inset(0 100% 0 0)' });
            once(t, () => gsap.to(t, { clipPath: 'inset(0 0% 0 0)', duration: 1.4, ease: 'power2.inOut' }));
        });
    });
}
