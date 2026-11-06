import { usePage } from '@inertiajs/vue3';
import type { SharedProps } from '@/types/content';

/** Placeholder for a form field by its `name`, from Settings › Form placeholders (shared Inertia prop). */
export function usePlaceholder() {
    const page = usePage<SharedProps>();
    return (name: string, fallback?: string): string | undefined => page.props.placeholders?.[name] || fallback;
}
