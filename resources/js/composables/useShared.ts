import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedProps } from '@/types/content';

export function useShared() {
    const page = usePage<SharedProps>();
    return {
        summit: computed(() => page.props.summit),
        site: computed(() => page.props.site),
        seo: computed(() => page.props.seo),
        locale: computed(() => page.props.locale),
    };
}
