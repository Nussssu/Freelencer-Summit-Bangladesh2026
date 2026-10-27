<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import { stage } from '@/stage/state';
import type { StubPage } from '@/types/content';

// One reusable page for every routed-but-unbuilt URL. Uses the real header/footer via the persistent layout.
defineProps<{ page: StubPage }>();
const { site } = useShared();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const related = computed(() => site.value.navigation.filter((n) => n.href !== '/').slice(0, 8));

let io: IntersectionObserver | undefined;
onMounted(() => {
    stage.step = 0;
    io = new IntersectionObserver(([e]) => (e.isIntersecting ? stage.visible.add('stub') : stage.visible.delete('stub')));
    io.observe(root.value!);
});
onBeforeUnmount(() => (io?.disconnect(), stage.visible.delete('stub')));
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <section ref="root" class="grain grid-lines relative flex min-h-[88svh] items-end pt-40 pb-16">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/90 to-night/30" aria-hidden="true" />
        <div class="container-site">
            <p class="t-label text-mint" data-reveal="fade">{{ t('page.comingNext') }}</p>
            <SplitHeading :text="page.title" as="h1" class="t-h2-xl mt-4 max-w-4xl" />
            <div class="stitch mt-8 max-w-md" data-reveal="stitch" />
            <p class="mt-8 max-w-xl text-lg" data-reveal="fade">{{ page.description }}</p>
            <p class="mt-4 max-w-xl text-muted" data-reveal="fade">{{ t('page.willHold') }}: {{ page.upcoming }}.</p>
            <div class="mt-10 flex flex-wrap gap-3" data-reveal="fade">
                <CtaLink href="/register">{{ t('cta.register') }}</CtaLink>
                <CtaLink href="/" tone="ghost">{{ t('brand.title') }}</CtaLink>
            </div>
        </div>
    </section>
    <section class="on-light section">
        <div class="container-site">
            <h2 class="t-label text-ink-2">{{ t('page.meanwhile') }}</h2>
            <ul class="mt-6 grid border-t border-line-light sm:grid-cols-2 lg:grid-cols-4">
                <li v-for="n in related" :key="n.href" class="border-b border-line-light sm:odd:border-r lg:border-r">
                    <Link :href="n.href" class="flex min-h-16 items-center justify-between p-4 font-display text-2xl font-black uppercase transition-colors duration-300 hover:bg-night hover:text-paper">{{ n.label }} <span aria-hidden="true">→</span></Link>
                </li>
            </ul>
        </div>
    </section>
</template>
