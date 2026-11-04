<script setup lang="ts">
import { computed } from 'vue';
import { AMENITIES, BOOTH_FILL, type Booth, ENTRANCE, FLOOR, PLAN_H, PLAN_W, STANDS, WALL, ZONES } from './booths';

// Exhibition hall drawn as one SVG in the venue-plan style: grey hall floor inside the walls, white stand islands with
// stand number and m², gradient pavilions with their tier tag, yellow amenity blocks, toilets and the entrance arrow.
// Stand status comes from the CMS booths (matched by code). `interactive` makes stands focusable/clickable (the
// zoomable dialog); the page preview stays static.
const props = defineProps<{ booths: Booth[]; interactive?: boolean; selected?: string | null }>();
const emit = defineEmits<{ select: [code: string] }>();

const status = computed(() => Object.fromEntries(props.booths.map((b) => [b.code, b.status])));
const pick = (code: string) => props.interactive && emit('select', code);
const zoneText = (lines: string[], cy: number) => lines.map((l, i) => ({ l, y: cy + (i - (lines.length - 1) / 2) * 19 + 14 }));
</script>

<template>
    <svg :viewBox="`0 0 ${PLAN_W} ${PLAN_H}`" class="block h-auto w-full select-none" role="img" aria-label="Exhibition floor plan with stands, pavilions and booth status" font-family="inherit">
        <defs>
            <linearGradient v-for="(z, i) in ZONES" :id="`fpz${i}`" :key="i" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" :stop-color="z.from" />
                <stop offset="1" :stop-color="z.to" />
            </linearGradient>
        </defs>

        <!-- Hall floor and walls -->
        <path :d="FLOOR" fill="#e3e6e9" />
        <path :d="WALL" fill="none" stroke="#1d262b" stroke-width="5" stroke-linejoin="miter" />

        <!-- Toilets in the top-wall bay -->
        <g fill="#fff" stroke="#1d262b" stroke-width="2.5">
            <rect v-for="(x, i) in [1165, 1215, 1265, 1310]" :key="i" :x="x" y="106" width="32" height="28" rx="3" />
        </g>
        <g font-size="12" font-weight="800" text-anchor="middle" fill="#1d262b">
            <text x="1181" y="125">WC</text><text x="1231" y="125">WC</text><text x="1281" y="125">WC</text><text x="1326" y="125">WC</text>
        </g>

        <!-- Pavilions -->
        <g v-for="(z, i) in ZONES" :key="z.lines.join()">
            <rect :x="z.x" :y="z.y" :width="z.w" :height="z.h" :fill="`url(#fpz${i})`" :stroke="z.gold ? '#eab94b' : 'none'" :stroke-width="z.gold ? 6 : 0" />
            <rect :x="z.x + z.w / 2 - 22" :y="z.y + z.h / 2 - 34 - (z.lines.length - 1) * 9" width="44" height="18" rx="2" fill="none" stroke="#fff" stroke-width="1.5" />
            <text :x="z.x + z.w / 2" :y="z.y + z.h / 2 - 21 - (z.lines.length - 1) * 9" text-anchor="middle" font-size="10.5" font-weight="700" fill="#fff">{{ z.tag }}</text>
            <text v-for="t in zoneText(z.lines, z.y + z.h / 2)" :key="t.l" :x="z.x + z.w / 2" :y="t.y" text-anchor="middle" font-size="16" font-weight="800" fill="#fff">{{ t.l }}</text>
        </g>

        <!-- Amenities -->
        <g v-for="a in AMENITIES" :key="a.label + a.x">
            <rect :x="a.x" :y="a.y" :width="a.w" :height="a.h" fill="#f9c21a" />
            <text
                v-if="a.vertical"
                :transform="`translate(${a.x + a.w / 2 + 5} ${a.y + a.h / 2}) rotate(90)`"
                text-anchor="middle"
                font-size="13"
                font-weight="800"
                fill="#1d262b"
            >{{ a.label }}</text>
            <template v-else-if="a.w < 60">
                <text v-for="(w, i) in a.label.split(' ')" :key="w" :x="a.x + a.w / 2" :y="a.y + a.h / 2 + (i - 0.5) * 15 + 5" text-anchor="middle" font-size="12" font-weight="800" fill="#1d262b">{{ w }}</text>
            </template>
            <text v-else :x="a.x + a.w / 2" :y="a.y + a.h / 2 + 6" text-anchor="middle" font-size="16" font-weight="800" fill="#1d262b">{{ a.label }}</text>
        </g>

        <!-- Stands -->
        <g
            v-for="s in STANDS"
            :key="s.code"
            :class="interactive && 'stand'"
            :tabindex="interactive ? 0 : undefined"
            :role="interactive ? 'button' : undefined"
            :aria-label="interactive ? `Stand ${s.code}, ${s.sqm} square metres, ${status[s.code] ?? 'available'}` : undefined"
            :aria-pressed="interactive ? selected === s.code : undefined"
            @click="pick(s.code)"
            @keydown.enter.space.prevent="pick(s.code)"
        >
            <rect
                :x="s.x"
                :y="s.y"
                :width="s.w"
                :height="s.h"
                :fill="selected === s.code ? '#7fe0c8' : (BOOTH_FILL[status[s.code] ?? 'available'] ?? '#fff')"
                :stroke="selected === s.code ? '#087f8c' : '#b9c1c7'"
                :stroke-width="selected === s.code ? 2.5 : 1"
            />
            <text :x="s.x + 4" :y="s.y + 12" font-size="10.5" font-weight="600" fill="#1d262b" pointer-events="none">{{ s.code }}</text>
            <text :x="s.x + s.w - 3" :y="s.y + s.h - 4" text-anchor="end" font-size="6.5" fill="#6b757c" pointer-events="none">{{ s.sqm }}m²</text>
        </g>

        <!-- Entrance -->
        <path :d="`M${ENTRANCE.x + 150} ${ENTRANCE.y + ENTRANCE.h / 2}H${ENTRANCE.x + 40}`" stroke="#1d262b" stroke-width="5" />
        <path :d="`M${ENTRANCE.x + 58} ${ENTRANCE.y + ENTRANCE.h / 2 - 14}L${ENTRANCE.x + 36} ${ENTRANCE.y + ENTRANCE.h / 2}L${ENTRANCE.x + 58} ${ENTRANCE.y + ENTRANCE.h / 2 + 14}Z`" fill="#1d262b" />
        <text :x="ENTRANCE.x + 40" :y="ENTRANCE.y + ENTRANCE.h / 2 - 26" font-size="30" font-weight="800" fill="#1d262b">Entrance</text>
    </svg>
</template>

<style scoped>
.stand {
    cursor: pointer;
    outline: none;
}
.stand:hover rect,
.stand:focus-visible rect {
    fill: #c9f2e6;
    stroke: #087f8c;
}
</style>
