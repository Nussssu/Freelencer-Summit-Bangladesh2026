<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { DeltaScene } from './scene';
import geo from './geo.json';
import { STAGE_OFF_CLASS, stage, stageMode } from './state';

// Lazily loaded (async chunk with three.js). Lives in the persistent layout, shared by hero and journey.
const props = defineProps<{ districts: number; beaconLabel: string }>();
const canvas = ref<HTMLCanvasElement>();
const labelEls: HTMLElement[] = [];
let scene: DeltaScene | undefined;

const onResize = () => scene?.resize();
const onPointer = (e: PointerEvent) => {
    stage.pointer.x = (e.clientX / innerWidth) * 2 - 1;
    stage.pointer.y = -(e.clientY / innerHeight) * 2 + 1;
    stage.pointer.active = true;
};
const onLeave = () => (stage.pointer.active = false);

onMounted(() => {
    try {
        scene = new DeltaScene({ canvas: canvas.value!, labels: labelEls, districts: props.districts });
    } catch {
        document.documentElement.classList.add(STAGE_OFF_CLASS);
        stageMode.value = 'off';
        return;
    }
    scene.start();
    stage.running = true;
    stageMode.value = 'on';
    document.documentElement.classList.add('stage-on');
    (window as unknown as { __stage?: DeltaScene }).__stage = scene; // QA hook: fps sampling
    addEventListener('resize', onResize);
    if (matchMedia('(pointer: fine)').matches) {
        addEventListener('pointermove', onPointer, { passive: true });
        document.documentElement.addEventListener('pointerleave', onLeave);
    }
});

onBeforeUnmount(() => {
    removeEventListener('resize', onResize);
    removeEventListener('pointermove', onPointer);
    document.documentElement.removeEventListener('pointerleave', onLeave);
    document.documentElement.classList.remove('stage-on');
    stage.running = false;
    scene?.dispose();
    scene = undefined;
});
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-0" aria-hidden="true">
        <canvas ref="canvas" class="stage-ink block h-full w-full" />
        <span
            v-for="(d, i) in [...geo.divisions.map((x) => x.name), beaconLabel]"
            :key="d"
            :ref="(el) => (labelEls[i] = el as HTMLElement)"
            class="t-label absolute top-0 left-0 -translate-x-1/2 whitespace-nowrap opacity-0"
            :class="i === geo.divisions.length ? 'bg-red px-2 py-1 text-night' : 'text-night/80'"
            >{{ d }}</span
        >
    </div>
</template>
