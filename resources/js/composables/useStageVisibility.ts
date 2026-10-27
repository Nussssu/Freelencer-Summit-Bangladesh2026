import { onBeforeUnmount, onMounted, type Ref } from 'vue';
import { stage } from '@/stage/state';

/** Registers a section that shows the shared canvas; the render loop pauses when none is on screen. */
export function useStageVisibility(el: Ref<HTMLElement | null | undefined>, key: string) {
    let io: IntersectionObserver | undefined;
    onMounted(() => {
        io = new IntersectionObserver(([e]) => (e.isIntersecting ? stage.visible.add(key) : stage.visible.delete(key)));
        if (el.value) io.observe(el.value);
    });
    onBeforeUnmount(() => {
        io?.disconnect();
        stage.visible.delete(key);
    });
}
