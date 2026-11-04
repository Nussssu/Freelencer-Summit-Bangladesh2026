<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InBtn from '@/components/inner/InBtn.vue';
import { useFocusTrap } from '@/composables/useFocusTrap';
import { pauseScroll, resumeScroll } from '@/composables/useLenis';
import { BOOTH_FILL, type Booth, PLAN_H, PLAN_W, STANDS } from './booths';
import FloorPlanMap from './FloorPlanMap.vue';

// Full-screen floor plan popup. The map can be zoomed (buttons, wheel, double-click, pinch, + / - / 0 keys) and dragged;
// clicking a booth shows its status card. Same modal mechanics as ui/Dialog: focus trap, Escape, scroll paused.
const props = defineProps<{ booths: Booth[]; legend: { value: string; label: string }[]; note?: string }>();
const open = defineModel<boolean>('open', { required: true });
const { t } = useI18n();

const panel = ref<HTMLElement>();
const viewport = ref<HTMLElement>();
const mounted = ref(false);
onMounted(() => {
    mounted.value = true;
    window.addEventListener('resize', onResize);
});
onBeforeUnmount(() => window.removeEventListener('resize', onResize));
const onResize = () => open.value && reset();
const close = () => (open.value = false);
useFocusTrap(panel, open, close);

const RATIO = PLAN_H / PLAN_W; // map height / width
const MAX = 4;
const v = reactive({ s: 1, x: 0, y: 0, fit: 1, w: 0, h: 0 });
const animate = ref(false);
const selected = ref<string | null>(null);
// Selected stand from the plan, with its status from the CMS booth of the same code (none = available)
const booth = computed(() => {
    const s = STANDS.find((x) => x.code === selected.value);
    if (!s) return null;
    const b = props.booths.find((x) => x.code === s.code);
    return { code: s.code, sqm: s.sqm, size: b?.size ?? null, status: b?.status ?? 'available' };
});
const label = (status: string) => props.legend.find((l) => l.value === status)?.label ?? status;

// The map's base size is the viewport width; scale 1 = fit width, `fit` = whole map visible. Zoom resizes the SVG
// (not a CSS scale) so the plan is redrawn sharp at every level.
function measure() {
    const el = viewport.value;
    if (!el) return;
    v.w = el.clientWidth;
    v.h = el.clientHeight;
    v.fit = Math.min(1, v.h / (v.w * RATIO));
}
function clamp() {
    const cw = v.w * v.s, ch = v.w * RATIO * v.s;
    v.x = cw <= v.w ? (v.w - cw) / 2 : Math.min(0, Math.max(v.w - cw, v.x));
    v.y = ch <= v.h ? (v.h - ch) / 2 : Math.min(0, Math.max(v.h - ch, v.y));
}
function zoomAt(next: number, cx = v.w / 2, cy = v.h / 2) {
    const s = Math.min(v.fit * MAX, Math.max(v.fit, next));
    v.x = cx - (cx - v.x) * (s / v.s);
    v.y = cy - (cy - v.y) * (s / v.s);
    v.s = s;
    clamp();
}
const step = (f: number) => ((animate.value = true), zoomAt(v.s * f));
function reset() {
    animate.value = true;
    measure();
    v.s = v.fit;
    clamp();
}
const pct = computed(() => Math.round((v.s / v.fit) * 100));

watch(open, async (o) => {
    document.documentElement.style.overflow = o ? 'hidden' : '';
    if (o) {
        pauseScroll();
        selected.value = null;
        await nextTick();
        animate.value = false;
        measure();
        v.s = v.fit;
        clamp();
    } else resumeScroll();
});

// Pointer handling: one pointer pans, two pinch. A press that moved is not a booth click.
const pts = new Map<number, { x: number; y: number }>();
const dragging = ref(false);
let moved = 0;
let captured = false;
let pinch = 0;
const local = (e: PointerEvent | WheelEvent | MouseEvent) => {
    const r = viewport.value!.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top };
};
function down(e: PointerEvent) {
    pts.set(e.pointerId, local(e));
    moved = 0;
    captured = false;
    dragging.value = true;
    animate.value = false;
    if (pts.size === 2) {
        const [a, b] = [...pts.values()];
        pinch = Math.hypot(a.x - b.x, a.y - b.y);
    }
}
function move(e: PointerEvent) {
    const prev = pts.get(e.pointerId);
    if (!prev) return;
    const p = local(e);
    if (pts.size === 1) {
        if (!captured && moved > 3) {
            viewport.value?.setPointerCapture(e.pointerId);
            captured = true;
        }
        v.x += p.x - prev.x;
        v.y += p.y - prev.y;
        clamp();
    }
    moved += Math.abs(p.x - prev.x) + Math.abs(p.y - prev.y);
    pts.set(e.pointerId, p);
    if (pts.size === 2) {
        const [a, b] = [...pts.values()];
        const d = Math.hypot(a.x - b.x, a.y - b.y);
        if (pinch) zoomAt(v.s * (d / pinch), (a.x + b.x) / 2, (a.y + b.y) / 2);
        pinch = d;
    }
}
function up(e: PointerEvent) {
    pts.delete(e.pointerId);
    if (pts.size < 2) pinch = 0;
    if (!pts.size) dragging.value = false;
}
function wheel(e: WheelEvent) {
    animate.value = false;
    const p = local(e);
    zoomAt(v.s * Math.exp(-e.deltaY * 0.0015), p.x, p.y);
}
function dbl(e: MouseEvent) {
    animate.value = true;
    const p = local(e);
    zoomAt(v.s * 1.8, p.x, p.y);
}
function select(code: string) {
    if (moved > 6) return;
    selected.value = selected.value === code ? null : code;
}
function keys(e: KeyboardEvent) {
    if (e.key === '+' || e.key === '=') step(1.4);
    else if (e.key === '-' || e.key === '_') step(1 / 1.4);
    else if (e.key === '0') reset();
    else return;
    e.preventDefault();
}
</script>

<template>
    <Teleport v-if="mounted" to="body">
        <Transition enter-active-class="transition-opacity duration-200" leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[75] bg-night/70" aria-hidden="true" @click="close" />
        </Transition>
        <Transition enter-active-class="transition duration-300" leave-active-class="transition duration-150" enter-from-class="opacity-0 translate-y-3" leave-to-class="opacity-0" @after-enter="reset">
            <div
                v-if="open"
                ref="panel"
                role="dialog"
                aria-modal="true"
                aria-labelledby="fp-title"
                data-lenis-prevent
                class="fixed inset-2 z-[76] mx-auto flex max-w-7xl flex-col overflow-hidden rounded-2xl bg-white text-night shadow-[0_30px_70px_-30px_rgb(7_20_17/0.55)] sm:inset-6"
            >
                <!-- Header: title, legend, close -->
                <div class="flex items-start justify-between gap-4 border-b border-night/10 px-4 py-3 sm:items-center sm:gap-6 sm:px-6 sm:py-4">
                    <div class="min-w-0 flex-1">
                        <h2 id="fp-title" class="text-xl font-extrabold uppercase sm:text-2xl">Exhibition <span class="text-green">floor plan</span></h2>
                        <p class="text-xs text-ink-2 sm:text-sm">Drag to move, scroll or pinch to zoom, select a stand for details.</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-4">
                        <ul class="hidden flex-wrap items-center gap-4 sm:flex">
                            <li v-for="l in legend" :key="l.value" class="flex items-center gap-2 text-sm font-semibold"><span class="size-3.5 rounded-full ring-1 ring-night/25" :style="{ background: BOOTH_FILL[l.value] }" aria-hidden="true" />{{ l.label }}</li>
                        </ul>
                        <button
                            type="button"
                            class="grid size-10 place-items-center rounded-full bg-night/5 text-night transition-colors hover:bg-night hover:text-white"
                            :aria-label="t('sections.close')"
                            data-autofocus
                            @click="close"
                        >
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Zoomable map -->
                <div
                    ref="viewport"
                    class="relative min-h-0 flex-1 touch-none overflow-hidden bg-white outline-none"
                    :class="dragging ? 'cursor-grabbing' : 'cursor-grab'"
                    tabindex="0"
                    aria-label="Floor plan map. Use plus and minus to zoom, zero to reset."
                    @pointerdown="down"
                    @pointermove="move"
                    @pointerup="up"
                    @pointercancel="up"
                    @wheel.prevent="wheel"
                    @dblclick="dbl"
                    @keydown="keys"
                >
                    <div
                        class="absolute top-0 left-0"
                        :class="animate && 'transition-[width,transform] duration-300 ease-out'"
                        :style="{ width: `${v.w * v.s}px`, transform: `translate(${v.x}px, ${v.y}px)` }"
                    >
                        <FloorPlanMap :booths="booths" interactive :selected="selected" @select="select" />
                    </div>

                    <!-- Zoom controls -->
                    <div class="absolute right-3 bottom-3 flex flex-col overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-night/10 sm:right-5 sm:bottom-5" @pointerdown.stop @dblclick.stop>
                        <button type="button" class="fp-ctl" aria-label="Zoom in" :disabled="v.s >= v.fit * MAX - 0.001" @click="step(1.4)">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                        </button>
                        <span class="border-y border-night/10 py-1 text-center text-[0.7rem] font-bold text-ink-2" aria-live="polite">{{ pct }}%</span>
                        <button type="button" class="fp-ctl" aria-label="Zoom out" :disabled="v.s <= v.fit + 0.001" @click="step(1 / 1.4)">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14" /></svg>
                        </button>
                        <button type="button" class="fp-ctl border-t border-night/10" aria-label="Reset view" @click="reset">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" /></svg>
                        </button>
                    </div>

                    <!-- Selected booth -->
                    <Transition enter-active-class="transition duration-200" leave-active-class="transition duration-150" enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0">
                        <div v-if="booth" class="absolute bottom-3 left-3 w-[min(18rem,calc(100%-5.5rem))] rounded-xl bg-white p-4 shadow-lg ring-1 ring-night/10 sm:bottom-5 sm:left-5" @pointerdown.stop @dblclick.stop>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-lg font-extrabold">Stand {{ booth.code }}</p>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold text-night ring-1 ring-night/20" :style="{ background: BOOTH_FILL[booth.status] }">{{ label(booth.status) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-ink-2">{{ booth.size ?? `${booth.sqm} m² space` }}</p>
                            <InBtn v-if="booth.status === 'available'" href="/exhibition/apply" tone="gold" class="mt-3">Book this stand</InBtn>
                        </div>
                    </Transition>
                </div>

                <!-- Footer: legend on phones, note -->
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-night/10 px-4 py-3 sm:px-6">
                    <ul class="flex flex-wrap items-center gap-4 sm:hidden">
                        <li v-for="l in legend" :key="l.value" class="flex items-center gap-2 text-xs font-semibold"><span class="size-3 rounded-full ring-1 ring-night/25" :style="{ background: BOOTH_FILL[l.value] }" aria-hidden="true" />{{ l.label }}</li>
                    </ul>
                    <p v-if="note" class="in-meta text-xs! sm:text-sm!">{{ note }}</p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fp-ctl {
    display: grid;
    place-items: center;
    width: 2.75rem;
    height: 2.75rem;
    color: var(--night);
    transition: background-color 0.2s ease;
}
.fp-ctl:hover:not(:disabled) {
    background: rgb(8 127 140 / 0.1);
}
.fp-ctl:disabled {
    opacity: 0.35;
    cursor: default;
}
</style>
