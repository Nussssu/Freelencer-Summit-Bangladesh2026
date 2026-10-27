import { ref } from 'vue';

/**
 * Shared, non-reactive scene state. ScrollTrigger callbacks write here; the render loop reads it
 * every frame. Deliberately a plain object: per-frame values never go through Vue reactivity.
 */
export const stage = {
    /** Journey progress: <=0 river flow (hero), 0.5–4 the four journey steps. */
    step: 0,
    /** Sections currently showing the canvas ('hero', 'journey', 'stub'); render loop pauses when empty. */
    visible: new Set<string>(),
    /** Pointer in NDC (-1..1), fine pointers only. */
    pointer: { x: 0, y: 0, active: false },
    /** True when the WebGL scene is running (hero swaps its poster/video out). */
    running: false,
};

export type Stage = typeof stage;

/** Set once by the layout/stage (not per frame): 'off' shows video/SVG fallbacks, 'on' hides them. */
export const stageMode = ref<'pending' | 'on' | 'off'>('pending');

/** html class toggled when the stage is unavailable, so sections can show their static fallbacks. */
export const STAGE_OFF_CLASS = 'stage-off';
