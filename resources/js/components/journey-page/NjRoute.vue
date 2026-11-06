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
// pins under the header and the scroll drives the map and the spine continuously: half a stretch of scroll brings the
// spine halfway to the next step, the rest lands it on that step, up to the Grand Summit, then it lets go; scrolling
// back runs it back. On small screens and under reduced motion the map plays campuses → districts →
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
const active = ref(-1);
// Spine fill in px: from the first dot, through each dot in turn, so a stage between two steps sits between their dots.
const items = ref<HTMLElement[]>([]);
const fill = ref(0);
const fillAt = (v: number) => {
    const dots = items.value.map((el) => el.offsetTop + 11.5);
    if (!dots.length || v <= AT[0]) return 0;
    const k = AT.findIndex((a, i) => i > 0 && v <= a);
    if (k < 0) return dots[dots.length - 1] - 8;
    return dots[k - 1] + ((v - AT[k - 1]) / (AT[k] - AT[k - 1])) * (dots[k] - dots[k - 1]) - 8;
};
// Stage value for a position 0…n-1 along the steps.
const stageAt = (f: number) => {
    const k = Math.min(AT.length - 2, Math.floor(f));
    return AT[k] + (f - k) * (AT[k + 1] - AT[k]);
};

const p = { v: 0 };
let tween: gsap.core.Tween | undefined;
const apply = () => {
    fill.value = fillAt(p.v);
    // A step lights up once the spine reaches it, not halfway there.
    active.value = p.v < 0.4 ? -1 : AT.filter((a) => a <= p.v + 0.02).length - 1 + Number(p.v < AT[0]);
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
// While pinned, a click scrolls to that step's point, so the scroll position and the map agree.
const pick = (i: number) => {
    if (!scrollMode.value || !pin) return goTo(i);
    window.scrollTo({ top: pin.start + (i / (props.steps.length - 1)) * (pin.end - pin.start), behavior: 'smooth' });
};
const onResize = () => apply();

let mm: gsap.MatchMedia | undefined;
onMounted(() => {
    addEventListener('resize', onResize);
    mm = gsap.matchMedia();
    mm.add({ scroll: '(min-width: 1024px) and (prefers-reduced-motion: no-preference)' }, (ctx) => {
        if (ctx.conditions?.scroll) {
            scrollMode.value = true;
            // Pinned just under the fixed header; about a screen of scroll from one step to the next (two scrolls: the
            // first brings the spine halfway, the second onto the next step). A short ease keeps it smooth, not jumpy.
            const head = () => document.querySelector('header')?.offsetHeight ?? 0;
            pin = ScrollTrigger.create({
                trigger: frame.value,
                start: () => `top top+=${head() + 24}`,
                end: () => `+=${innerHeight * 0.9 * (props.steps.length - 1)}`,
                pin: true,
                anticipatePin: 1,
                onUpdate: (t) => {
                    tween?.kill();
                    tween = gsap.to(p, { v: stageAt(t.progress * (props.steps.length - 1)), duration: 0.35, ease: 'power3.out', onUpdate: apply });
                },
            });
            goTo(0);
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
    removeEventListener('resize', onResize);
    mm?.revert();
    tween?.kill();
});
</script>

<template>
    <section ref="root" class="section overflow-x-clip" aria-labelledby="nj-route">
        <div ref="frame" class="container-site">
            <!-- Heading across the full width, one line on desktop, pinned with the map and steps -->
            <SectionHead id="nj-route" :heading="heading" class="lg:[&_h2]:max-w-none lg:[&_h2]:whitespace-nowrap" />
            <div class="grid gap-12 lg:grid-cols-12 lg:items-center">
                <div class="relative lg:col-span-7">
                    <!-- Soft halo behind Dhaka so the converging lines have somewhere to land -->
                    <div class="pointer-events-none absolute top-[38%] left-[44%] size-[55%] -translate-1/2 rounded-full bg-[radial-gradient(closest-side,rgb(234_185_75/0.22),transparent)]" aria-hidden="true" />
                    <JourneyMapStages ref="map" scrubbed :beacon-label="beaconLabel" />
                </div>

                <!-- Steps start at the top of the map (not centred on it), so the last one sits well inside the screen -->
                <div class="lg:col-span-5 lg:self-start lg:pt-6">
                    <ol class="relative">
                        <!-- Spine: hairline track, filled teal → bronze as the map advances -->
                        <span class="absolute top-2 bottom-2 left-[7px] w-px bg-line-light" aria-hidden="true" />
                        <span class="absolute top-2 left-[7px] w-px bg-gradient-to-b from-green to-gold" :style="{ height: `${fill}px` }" aria-hidden="true" />
                        <li
                            v-for="(st, i) in steps"
                            ref="items"
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
        </div>
    </section>
</template>

<style scoped>
/* A smaller heading than the page's other sections, so the pinned block keeps room for the map and every step */
:deep(#nj-route) {
    font-size: clamp(1.5rem, 2.3vw, 2.125rem);
}
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
