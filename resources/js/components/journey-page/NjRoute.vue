<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import JourneyMapStages from '@/components/sections/journey/JourneyMapStages.vue';
import Icon from '@/components/ui/Icon.vue';
import { ScrollTrigger, gsap, reducedMotion } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { JourneyStep } from '@/types/content';
import type { SectionHeading } from '@/types/inner';

// Map and a compact step list (title + link) as one instrument that fits a single screen. On desktop the whole block
// pins under the header while scrolling advances the map one stage at a time, up to the Grand Summit, then lets go;
// scrolling back steps it back. On small screens and under reduced motion the map plays campuses → districts →
// divisions → Dhaka once in view instead. Click a step to go to its stage (hover and focus too, off desktop).
const props = defineProps<{ heading: SectionHeading; steps: JourneyStep[]; beaconLabel: string }>();
const root = ref<HTMLElement>();
const frame = ref<HTMLElement>();
const map = ref<InstanceType<typeof JourneyMapStages>>();
const scrollMode = ref(false);
let pin: ScrollTrigger | undefined;
useReveal(root);

// Stage centres on JourneyMapStages' 0–4 scale, and where each stage hands over to the next.
const AT = [1, 2, 2.95, 4];
const stepOf = (s: number) => (s < 1.5 ? 0 : s < 2.5 ? 1 : s < 3.4 ? 2 : 3);
const s = ref(0);
const active = ref(-1);

const p = { v: 0 };
let tween: gsap.core.Tween | undefined;
const apply = () => {
    s.value = p.v;
    active.value = p.v < 0.4 ? -1 : stepOf(p.v);
    map.value?.setStep(p.v);
};
const play = (to: number, duration: number) => {
    tween?.kill();
    if (reducedMotion()) {
        p.v = to;
        return apply();
    }
    tween = gsap.to(p, { v: to, duration, ease: duration > 3 ? 'none' : 'power2.inOut', onUpdate: apply });
};
const goTo = (i: number) => play(AT[i], 0.9);
// While pinned, a click scrolls to the middle of that step's stretch, so the scroll position and the map agree.
const pick = (i: number) => {
    if (!scrollMode.value || !pin) return goTo(i);
    window.scrollTo({ top: pin.start + ((i + 0.5) / props.steps.length) * (pin.end - pin.start), behavior: 'smooth' });
};

let mm: gsap.MatchMedia | undefined;
onMounted(() => {
    mm = gsap.matchMedia();
    mm.add({ scroll: '(min-width: 1024px) and (prefers-reduced-motion: no-preference)' }, (ctx) => {
        if (ctx.conditions?.scroll) {
            scrollMode.value = true;
            // Pinned just under the fixed header; about half a screen of scroll per stage.
            const head = () => document.querySelector('header')?.offsetHeight ?? 0;
            let shown = -1;
            pin = ScrollTrigger.create({
                trigger: frame.value,
                start: () => `top top+=${head() + 24}`,
                end: () => `+=${innerHeight * 0.45 * props.steps.length}`,
                pin: true,
                anticipatePin: 1,
                onUpdate: (t) => {
                    const i = Math.min(props.steps.length - 1, Math.floor(t.progress * props.steps.length));
                    if (i !== shown) goTo((shown = i));
                },
            });
            return () => {
                pin?.kill();
                pin = undefined;
                scrollMode.value = false;
            };
        }
        const io = new IntersectionObserver(
            ([e]) => {
                if (!e.isIntersecting) return;
                io.disconnect();
                play(4, 8);
            },
            { threshold: 0.35 },
        );
        io.observe(root.value!);
        return () => io.disconnect();
    });
});
onBeforeUnmount(() => {
    mm?.revert();
    tween?.kill();
});
</script>

<template>
    <section ref="root" class="section overflow-x-clip" aria-labelledby="nj-route">
        <div ref="frame" class="container-site grid gap-12 lg:grid-cols-12 lg:items-center">
            <div class="relative lg:col-span-7">
                <!-- Soft halo behind Dhaka so the converging lines have somewhere to land -->
                <div class="pointer-events-none absolute top-[38%] left-[44%] size-[55%] -translate-1/2 rounded-full bg-[radial-gradient(closest-side,rgb(234_185_75/0.22),transparent)]" aria-hidden="true" />
                <JourneyMapStages ref="map" scrubbed :beacon-label="beaconLabel" />
                <p class="mt-2 text-center text-sm text-ink-2" aria-live="polite">
                    <span class="font-semibold text-night">{{ active < 0 ? steps[0].title : steps[active].title }}</span>
                    <span class="mx-2 text-gold" aria-hidden="true">/</span>{{ active < 0 ? steps[0].tag : steps[active].tag }}
                </p>
            </div>

            <div class="lg:col-span-5">
                <SectionHead id="nj-route" :heading="heading" />
                <ol class="relative mt-10">
                    <!-- Spine: hairline track, filled teal → bronze as the map advances -->
                    <span class="absolute top-2 bottom-2 left-[7px] w-px bg-line-light" aria-hidden="true" />
                    <span class="absolute top-2 bottom-2 left-[7px] w-px origin-top bg-gradient-to-b from-green to-gold" :style="{ transform: `scaleY(${Math.min(1, s / 4)})` }" aria-hidden="true" />
                    <li
                        v-for="(st, i) in steps"
                        :key="st.title"
                        class="relative cursor-pointer pb-6 pl-10 transition-opacity duration-500 last:pb-0"
                        :class="active === -1 || active === i ? 'opacity-100' : 'opacity-55 hover:opacity-100'"
                        data-reveal="fade"
                        @mouseenter="!scrollMode && goTo(i)"
                        @focusin="!scrollMode && goTo(i)"
                        @click="pick(i)"
                    >
                        <span class="absolute top-1 left-0 grid size-[15px] place-items-center" aria-hidden="true">
                            <span v-if="active === i" class="nj-ping absolute inset-0 rounded-full" :class="i === steps.length - 1 ? 'bg-red/40' : 'bg-green/40'" />
                            <span class="relative size-[15px] rounded-full border-[3px] border-paper transition-colors duration-500" :class="active >= i ? (i === steps.length - 1 ? 'bg-red' : 'bg-green') : 'bg-line-light'" />
                        </span>
                        <h3 class="font-display text-[clamp(1.75rem,2.6vw,2.25rem)] leading-none font-black uppercase transition-colors duration-500" :class="active === i && 'text-green'">{{ st.title }}</h3>
                        <Link :href="st.href" class="link-u t-label mt-2 inline-flex min-h-11 items-center gap-2 text-green" @click.stop>{{ st.tag }} <Icon name="arrow" :size="14" /></Link>
                    </li>
                </ol>
            </div>
        </div>
    </section>
</template>

<style scoped>
.nj-ping {
    animation: nj-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes nj-ping {
    75%,
    100% {
        transform: scale(2.4);
        opacity: 0;
    }
}
@media (prefers-reduced-motion: reduce) {
    .nj-ping {
        animation: none;
    }
}
</style>
