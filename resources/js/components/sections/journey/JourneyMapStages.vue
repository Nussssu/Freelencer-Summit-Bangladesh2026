<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { gsap, reducedMotion } from '@/composables/motion';
import areaData from './journeyMapAreas.json';

type Named = [name: string, x: number, y: number];
const areas = areaData as { campuses: [number, number][]; districts: Named[]; divisions: Named[]; dhaka: string };

// National Journey map from one illustrated image. The whole map shows as a pale, solid version of it;
// each journey step reveals the realistic image only where that step's places are: 100 campuses,
// 12 districts, all 8 divisions, then Dhaka alone. Area coordinates are in image pixels
// (geo data warped onto the illustration). `scrubbed`: the parent drives setStep() from its pinned scroll;
// otherwise it plays once in view.
const props = defineProps<{ beaconLabel: string; scrubbed?: boolean }>();

const IMG = { src: '/media/journey/bangladesh-map.webp', w: 1340, h: 880 };
const smooth = (a: number, b: number, x: number) => {
    const t = Math.min(1, Math.max(0, (x - a) / (b - a)));
    return t * t * (3 - 2 * t);
};
const win = (a: number, b: number, c: number, d: number, s: number) => smooth(a, b, s) * (1 - smooth(c, d, s));

// Same step scale as JourneySection: 0.5–1.5 campuses, 1.5–2.5 districts, 2.5–3.4 divisions, 3.4–4 Dhaka.
const STAGES = [
    (s: number) => win(0.3, 0.55, 1.4, 1.6, s),
    (s: number) => win(1.4, 1.6, 2.4, 2.6, s),
    (s: number) => win(2.4, 2.6, 3.3, 3.5, s),
    (s: number) => smooth(3.3, 3.5, s),
];

const root = ref<HTMLElement>();
// One mask group and one label group per stage, in STAGES order.
const masks: SVGGElement[] = [];
const labels: SVGGElement[] = [];

// Connections into Dhaka from each step's places. The illustration already has the division lines and markers,
// so the divisions step shows those; the other steps draw matching ones.
const [, dx, dy] = areas.divisions[0];
const arc = ([x, y]: [number, number]) => {
    const len = Math.hypot(dx - x, dy - y);
    return { x, y, d: `M${x},${y}Q${(x + dx) / 2},${(y + dy) / 2 - len * 0.35} ${dx},${dy}` };
};
const others = areas.divisions.slice(1).map(([, x, y]) => arc([x, y]));
const ROUTES = [
    { kind: 'campus', from: 0.3, lines: areas.campuses.filter(([x, y]) => Math.hypot(dx - x, dy - y) > 40).map(arc) },
    { kind: 'district', from: 1.4, lines: areas.districts.map(([, x, y]) => arc([x, y])) },
    { kind: 'division', from: 3.3, lines: others },
];
const routeEls: SVGGElement[] = [];

const setStep = (s: number) => {
    STAGES.forEach((show, i) => {
        const o = show(s).toFixed(3);
        masks[i]?.setAttribute('opacity', o);
        labels[i]?.setAttribute('opacity', o);
    });
    // Each step's lines draw into Dhaka as it scrolls in, then a light pulse runs along them.
    ROUTES.forEach((r, k) => {
        const g = routeEls[k];
        if (!g) return;
        const n = r.lines.length;
        g.querySelectorAll<SVGPathElement>('[data-draw]').forEach((p, i) => {
            const at = r.from + ((i % n) / n) * 0.15;
            p.style.strokeDashoffset = String(1 - smooth(at, at + 0.3, s));
        });
        g.querySelectorAll<SVGPathElement>('.jm-route-pulse').forEach((p) => (p.style.opacity = smooth(r.from + 0.45, r.from + 0.55, s).toFixed(3)));
    });
};
defineExpose({ setStep });

let io: IntersectionObserver | undefined;
let tween: gsap.core.Tween | undefined;
onMounted(() => {
    setStep(0);
    if (props.scrubbed) return;
    // Not scrubbed (mobile / reduced motion): step through once in view, or show the finale straight away.
    io = new IntersectionObserver(([e]) => {
        if (!e.isIntersecting) return;
        io?.disconnect();
        if (reducedMotion()) return setStep(4);
        const p = { v: 0 };
        tween = gsap.to(p, { v: 4, duration: 8, ease: 'none', delay: 0.3, onUpdate: () => setStep(p.v) });
    });
    io.observe(root.value!);
});
onBeforeUnmount(() => {
    io?.disconnect();
    tween?.kill();
});
</script>

<template>
    <figure ref="root" class="jm-stages relative m-0 w-full" :aria-label="`Map of Bangladesh: campuses, districts and divisions converging on Dhaka, ${beaconLabel}`">
        <svg :viewBox="`0 0 ${IMG.w} ${IMG.h}`" class="block h-auto w-full" aria-hidden="true">
            <defs>
                <!-- Solid map: the same illustration, desaturated and lifted to a pale clay model -->
                <filter id="jm-solid" color-interpolation-filters="sRGB">
                    <feColorMatrix type="saturate" values="0" />
                    <feComponentTransfer>
                        <feFuncR type="linear" slope="0.42" intercept="0.6" />
                        <feFuncG type="linear" slope="0.42" intercept="0.585" />
                        <feFuncB type="linear" slope="0.42" intercept="0.55" />
                    </feComponentTransfer>
                </filter>
                <filter id="jm-feather" x="-5%" y="-5%" width="110%" height="110%">
                    <feGaussianBlur stdDeviation="5" />
                </filter>
                <mask id="jm-reveal" maskUnits="userSpaceOnUse" x="0" y="0" :width="IMG.w" :height="IMG.h">
                    <g filter="url(#jm-feather)" fill="#fff">
                        <g :ref="(el) => (masks[0] = el as SVGGElement)" opacity="0"><circle v-for="([x, y], i) in areas.campuses" :key="i" :cx="x" :cy="y" r="24" /></g>
                        <g :ref="(el) => (masks[1] = el as SVGGElement)" opacity="0"><circle v-for="[n, x, y] in areas.districts" :key="n" :cx="x" :cy="y" r="62" /></g>
                        <g :ref="(el) => (masks[2] = el as SVGGElement)" opacity="0"><rect :width="IMG.w" :height="IMG.h" /></g>
                        <g :ref="(el) => (masks[3] = el as SVGGElement)" opacity="0"><path :d="areas.dhaka" /><ellipse cx="598" cy="380" rx="185" ry="110" /></g>
                    </g>
                </mask>
            </defs>

            <image :href="IMG.src" :width="IMG.w" :height="IMG.h" filter="url(#jm-solid)" />
            <image :href="IMG.src" :width="IMG.w" :height="IMG.h" mask="url(#jm-reveal)" />

            <!-- Names for the places each step reveals -->
            <g class="jm-names">
                <g :ref="(el) => (labels[0] = el as SVGGElement)" opacity="0">
                    <g :ref="(el) => (routeEls[0] = el as SVGGElement)" class="jm-routes" :class="`is-${ROUTES[0].kind}`">
                        <path v-for="(r, i) in ROUTES[0].lines" :key="`g${i}`" :d="r.d" pathLength="1" data-draw class="jm-route-glow" />
                        <path v-for="(r, i) in ROUTES[0].lines" :key="`l${i}`" :d="r.d" pathLength="1" data-draw class="jm-route" />
                        <path v-for="(r, i) in ROUTES[0].lines" :key="`p${i}`" :d="r.d" pathLength="1" class="jm-route-pulse" :style="{ animationDelay: `${-i * 0.37}s` }" />
                        <circle v-for="(r, i) in ROUTES[0].lines" :key="`c${i}`" :cx="r.x" :cy="r.y" class="jm-route-dot" />
                    </g>
                </g>
                <g :ref="(el) => (labels[1] = el as SVGGElement)" opacity="0">
                    <g :ref="(el) => (routeEls[1] = el as SVGGElement)" class="jm-routes" :class="`is-${ROUTES[1].kind}`">
                        <path v-for="(r, i) in ROUTES[1].lines" :key="`g${i}`" :d="r.d" pathLength="1" data-draw class="jm-route-glow" />
                        <path v-for="(r, i) in ROUTES[1].lines" :key="`l${i}`" :d="r.d" pathLength="1" data-draw class="jm-route" />
                        <path v-for="(r, i) in ROUTES[1].lines" :key="`p${i}`" :d="r.d" pathLength="1" class="jm-route-pulse" :style="{ animationDelay: `${-i * 0.37}s` }" />
                        <circle v-for="(r, i) in ROUTES[1].lines" :key="`c${i}`" :cx="r.x" :cy="r.y" class="jm-route-dot" />
                    </g>
                    <text v-for="[n, x, y] in areas.districts" :key="n" :x="x" :y="y + 44" text-anchor="middle">{{ n }}</text>
                </g>
                <g :ref="(el) => (labels[2] = el as SVGGElement)" opacity="0">
                    <text v-for="[n, x, y] in areas.divisions.slice(1)" :key="n" :x="x" :y="y + 36" text-anchor="middle">{{ n }}</text>
                </g>
                <g :ref="(el) => (labels[3] = el as SVGGElement)" opacity="0">
                    <g :ref="(el) => (routeEls[2] = el as SVGGElement)" class="jm-routes" :class="`is-${ROUTES[2].kind}`">
                        <path v-for="(r, i) in ROUTES[2].lines" :key="`g${i}`" :d="r.d" pathLength="1" data-draw class="jm-route-glow" />
                        <path v-for="(r, i) in ROUTES[2].lines" :key="`l${i}`" :d="r.d" pathLength="1" data-draw class="jm-route" />
                        <path v-for="(r, i) in ROUTES[2].lines" :key="`p${i}`" :d="r.d" pathLength="1" class="jm-route-pulse" :style="{ animationDelay: `${-i * 0.37}s` }" />
                        <circle v-for="(r, i) in ROUTES[2].lines" :key="`c${i}`" :cx="r.x" :cy="r.y" class="jm-route-dot" />
                    </g>
                    <g :transform="`translate(${areas.divisions[0][1]} ${areas.divisions[0][2]})`">
                        <circle class="jm-route-dot jm-hub" />
                        <text y="-30" text-anchor="middle" class="jm-dhaka">Dhaka</text>
                    </g>
                </g>
            </g>
        </svg>
    </figure>
</template>

<style scoped>
.jm-stages {
    max-width: min(100%, calc((100svh - var(--header-h) - 24px) * 1340 / 880));
    margin-inline: auto;
}
.jm-names text {
    font-family: var(--font-mono, ui-monospace, monospace);
    font-size: 19px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    fill: #1f2d33;
    paint-order: stroke;
    stroke: rgb(255 255 255 / 0.85);
    stroke-width: 4px;
    stroke-linejoin: round;
}
.jm-names .jm-dhaka {
    font-size: 24px;
    fill: #0b6f77;
}
/* Grand Summit connections: soft glow, crisp light-blue line, a light pulse travelling into Dhaka */
.jm-routes path {
    fill: none;
    stroke-linecap: round;
    stroke-dasharray: 1;
    stroke-dashoffset: 1;
}
.jm-route-glow {
    stroke: rgb(8 127 140 / 0.35);
    stroke-width: 12;
    filter: blur(4px);
}
.jm-route {
    stroke: #087f8c;
    stroke-width: 3;
}
.jm-routes .jm-route-pulse {
    stroke: #bfe9ed;
    stroke-width: 5;
    stroke-dasharray: 0.06 0.94;
    opacity: 0;
    animation: jm-flow 2.6s linear infinite;
}
@keyframes jm-flow {
    from {
        stroke-dashoffset: 0.06;
    }
    to {
        stroke-dashoffset: -0.94;
    }
}
/* Markers like the illustration's: teal ring (#087F8C), white core */
.jm-route-dot {
    fill: #ffffff;
    stroke: #087f8c;
    stroke-width: 6;
    r: 11px;
    filter: drop-shadow(0 0 6px rgb(8 127 140 / 0.6));
}
.jm-hub {
    r: 16px;
    stroke-width: 8px;
}
/* 100 campus lines: finer and fainter so they converge without covering the map */
.jm-routes.is-campus .jm-route {
    stroke-width: 1.6;
    opacity: 0.75;
}
.jm-routes.is-campus .jm-route-glow,
.jm-routes.is-campus .jm-route-pulse {
    display: none;
}
.jm-routes.is-campus .jm-route-dot {
    r: 6px;
    stroke-width: 3;
}
@media (prefers-reduced-motion: reduce) {
    .jm-routes .jm-route-pulse {
        animation: none;
        display: none;
    }
}
</style>
