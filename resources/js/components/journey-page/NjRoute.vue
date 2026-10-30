<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import JourneyMapStages from '@/components/sections/journey/JourneyMapStages.vue';
import Icon from '@/components/ui/Icon.vue';
import { gsap, reducedMotion } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { JourneyStep } from '@/types/content';
import type { SectionHeading } from '@/types/inner';

// Map and timeline as one instrument: the map plays campuses → districts → divisions → Dhaka once in view, and the
// timeline lights the matching step and fills its spine. Hover, focus or click a step to send the map to that stage.
defineProps<{ heading: SectionHeading; steps: JourneyStep[]; beaconLabel: string }>();
const root = ref<HTMLElement>();
const map = ref<InstanceType<typeof JourneyMapStages>>();
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

let io: IntersectionObserver | undefined;
onMounted(() => {
    io = new IntersectionObserver(
        ([e]) => {
            if (!e.isIntersecting) return;
            io?.disconnect();
            play(4, 8);
        },
        { threshold: 0.35 },
    );
    io.observe(root.value!);
});
onBeforeUnmount(() => {
    io?.disconnect();
    tween?.kill();
});
</script>

<template>
    <section ref="root" class="section overflow-hidden bg-paper" aria-labelledby="nj-route">
        <div class="container-site grid gap-12 lg:grid-cols-12 lg:items-center">
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
                        class="relative cursor-pointer pb-7 pl-10 transition-opacity duration-500 last:pb-0"
                        :class="active === -1 || active === i ? 'opacity-100' : 'opacity-55 hover:opacity-100'"
                        data-reveal="fade"
                        @mouseenter="goTo(i)"
                        @focusin="goTo(i)"
                        @click="goTo(i)"
                    >
                        <span class="absolute top-1 left-0 grid size-[15px] place-items-center" aria-hidden="true">
                            <span v-if="active === i" class="nj-ping absolute inset-0 rounded-full" :class="i === steps.length - 1 ? 'bg-red/40' : 'bg-green/40'" />
                            <span class="relative size-[15px] rounded-full border-[3px] border-paper transition-colors duration-500" :class="active >= i ? (i === steps.length - 1 ? 'bg-red' : 'bg-green') : 'bg-line-light'" />
                        </span>
                        <p class="t-label text-ink-2">Stage {{ i + 1 }} of {{ steps.length }}</p>
                        <h3 class="mt-2 font-display text-[clamp(1.75rem,2.6vw,2.25rem)] leading-none font-black uppercase transition-colors duration-500" :class="active === i && 'text-green'">{{ st.title }}</h3>
                        <p class="mt-2 max-w-md text-ink-2">{{ st.text }}</p>
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
