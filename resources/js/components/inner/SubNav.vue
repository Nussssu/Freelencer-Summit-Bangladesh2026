<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { scrollToTarget } from '@/composables/useLenis';

// Sticky in-page navigation under the header; highlights the section in view.
// Solid background on purpose: the header samples the band under it and cannot read translucent (oklab) colours.
const props = defineProps<{ items: { id: string; label: string }[] }>();
const { t } = useI18n();
const active = ref(props.items[0]?.id);
let io: IntersectionObserver | undefined;

onMounted(() => {
    io = new IntersectionObserver((entries) => entries.forEach((e) => e.isIntersecting && (active.value = e.target.id)), { rootMargin: '-35% 0px -60% 0px' });
    props.items.forEach((i) => {
        const el = document.getElementById(i.id);
        if (el) io!.observe(el);
    });
});
onBeforeUnmount(() => io?.disconnect());

function go(id: string) {
    const el = document.getElementById(id);
    if (!el) return;
    scrollToTarget(el);
    history.replaceState(history.state, '', `#${id}`);
}
</script>

<template>
    <nav :aria-label="t('inner.subnav')" class="on-light sticky top-16 z-20 border-b border-line-light bg-paper md:top-[var(--header-h)]">
        <ul class="container-site flex gap-1 overflow-x-auto [scrollbar-width:none]">
            <li v-for="i in items" :key="i.id" class="shrink-0">
                <a
                    :href="`#${i.id}`"
                    class="relative flex min-h-12 items-center px-3 text-sm font-semibold uppercase transition-colors duration-300 hover:text-green"
                    :class="active === i.id ? 'text-green after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:bg-green' : 'text-ink-2'"
                    :aria-current="active === i.id ? 'location' : undefined"
                    @click.prevent="go(i.id)"
                    >{{ i.label }}</a
                >
            </li>
        </ul>
    </nav>
</template>
