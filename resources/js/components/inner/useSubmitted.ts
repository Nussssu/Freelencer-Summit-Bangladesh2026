import { usePage } from '@inertiajs/vue3';
import { computed, nextTick } from 'vue';
import { scrollToTarget } from '@/composables/useLenis';
import type { SharedProps } from '@/types/content';

/**
 * Reference number flashed by SubmissionController for this form (null until it is submitted), plus a helper that
 * brings the confirmation panel into view after a successful post.
 */
export function useSubmitted(form: string, anchor = '#form') {
    const page = usePage<SharedProps>();
    const reference = computed(() => (page.props.flash?.submitted?.form === form ? page.props.flash.submitted.reference : null));
    // The confirmation is shorter than the form, so jump (not glide) to it once Vue has swapped them.
    const reveal = async () => {
        await nextTick();
        requestAnimationFrame(() => {
            const el = document.querySelector<HTMLElement>(anchor);
            if (el) scrollToTarget(el, true);
        });
    };
    return { reference, reveal };
}
