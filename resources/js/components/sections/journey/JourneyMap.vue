<script setup lang="ts">
import geo from '@/stage/geo.json';

// Static SVG map from the same geo data as the 3D scene: mobile illustration and no-WebGL fallback.
const props = withDefaults(defineProps<{ step?: number; beaconLabel: string }>(), { step: 3 });
const VB = { x: -0.78, y: -1.05, w: 1.6, h: 2.1 };
const path = (ring: number[][]) => 'M' + ring.map(([x, y]) => `${x.toFixed(3)},${(-y).toFixed(3)}`).join('L') + 'Z';
const dhaka = geo.divisions[0].capital;
const arc = ([x, y]: number[]) => {
    const mx = (x + dhaka[0]) / 2, my = -(y + dhaka[1]) / 2 - Math.hypot(x - dhaka[0], y - dhaka[1]) * 0.35;
    return `M${x},${-y}Q${mx},${my} ${dhaka[0]},${-dhaka[1]}`;
};
</script>

<template>
    <svg :viewBox="`${VB.x} ${VB.y} ${VB.w} ${VB.h}`" class="h-auto w-full" role="img" :aria-label="`Map of Bangladesh: ${geo.divisions.length} divisions joined to Dhaka`">
        <path v-for="r in geo.national" :key="r.length + r[0][0]" :d="path(r)" fill="rgb(8 112 122 / .1)" stroke="var(--night)" stroke-opacity=".35" stroke-width=".004" />
        <path v-for="d in geo.divisions" :key="d.name" :d="path(d.ring)" fill="none" stroke="var(--green)" :stroke-opacity="props.step >= 2 ? 0.35 : 0" stroke-width=".003" class="transition-[stroke-opacity] duration-500" />
        <g :opacity="props.step === 0 ? 1 : 0.25" class="transition-opacity duration-500">
            <circle v-for="(c, i) in geo.campuses" :key="i" :cx="c[0]" :cy="-c[1]" r=".006" fill="var(--green)" />
        </g>
        <g v-if="props.step >= 3">
            <path v-for="d in geo.divisions.slice(1)" :key="d.name" :d="arc(d.capital)" fill="none" stroke="var(--gold-ink)" stroke-width=".006" stroke-dasharray=".02 .013" stroke-linecap="round" />
        </g>
        <g>
            <g v-for="(d, i) in geo.divisions" :key="d.name">
                <circle :cx="d.capital[0]" :cy="-d.capital[1]" :r="i === 0 ? 0.03 : 0.014" :fill="i === 0 ? 'var(--red)' : 'var(--gold-ink)'" />
                <text :x="d.capital[0] + 0.03" :y="-d.capital[1] + 0.012" font-size=".045" class="font-mono uppercase" fill="var(--night)">{{ d.name }}</text>
            </g>
            <text :x="dhaka[0] - 0.05" :y="-dhaka[1] - 0.06" font-size=".05" class="font-mono uppercase" fill="var(--red)">{{ beaconLabel }}</text>
        </g>
    </svg>
</template>
