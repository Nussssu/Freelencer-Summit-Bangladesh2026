<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useShared } from '@/composables/useShared';
import type { Trust } from '@/types/content';

// Section 2. Organiser & partner strip under the hero: BACCO × DoICT static on the left, then every partner
// category (label + logos) as columns in one shared, seamless marquee. Hover pauses it; under reduced motion it is
// swapped for the same columns as a static, wrapped list (which also serves screen readers, since the moving copies
// are aria-hidden).
const props = defineProps<{ trust: Trust }>();
const { site } = useShared();
const { t } = useI18n();
const groups = computed(() => props.trust.groups.filter((g) => g.partners.length));
// ~5s per logo keeps the drift slow whatever the partner count.
const duration = computed(() => `${groups.value.reduce((n, g) => n + g.partners.length, 0) * 5}s`);

// Organiser logos come in very different shapes (BACCO wide, DoICT tall), so size each by its aspect ratio to the
// same visual area instead of the same height.
const orgRow = ref<HTMLElement>();
const ratios = ref<number[]>([]);
const orgLogoHeight = (i: number) => (ratios.value[i] ? `${Math.min(56, Math.round(54 / Math.sqrt(ratios.value[i])))}px` : undefined);
onMounted(() =>
    orgRow.value?.querySelectorAll('img').forEach((img, i) => {
        const read = () => img.naturalWidth && (ratios.value[i] = img.naturalWidth / img.naturalHeight);
        if (img.complete) read();
        else img.addEventListener('load', read, { once: true });
    }),
);
</script>

<template>
    <section data-section="2" :aria-label="t('sections.trust')" class="trust on-light relative z-[1] flex flex-col lg:flex-row">
        <div class="trust-col trust-org shrink-0 px-6 py-6 md:px-10">
            <h2 class="trust-label">{{ t('sections.jointly') }}</h2>
            <ul ref="orgRow" class="flex items-center gap-8">
                <li v-for="(o, i) in site.organisers" :key="o.short" class="flex h-14 items-center">
                    <img v-if="o.logo" :src="o.logo" :alt="o.name" class="h-10 w-auto" :style="{ height: orgLogoHeight(i) }" />
                    <abbr v-else :title="o.name" class="font-display text-[2rem] leading-none font-black uppercase no-underline" :class="o.short === 'BACCO' && 'text-bacco'">{{ o.short }}</abbr>
                </li>
            </ul>
        </div>

        <div class="flex min-w-0 flex-1 items-stretch py-6">
            <div class="trust-marquee w-full overflow-hidden" aria-hidden="true">
                <div class="marquee h-full" :style="{ '--marquee-dur': duration }">
                    <div v-for="copy in 2" :key="copy" class="flex shrink-0 items-stretch">
                        <div v-for="g in groups" :key="g.label" class="trust-col trust-group px-8">
                            <span class="trust-label">{{ g.label }}</span>
                            <div class="flex h-14 items-center gap-10">
                                <template v-for="p in g.partners" :key="p.name">
                                    <img v-if="p.logo" :src="p.logo" alt="" loading="lazy" class="trust-logo" />
                                    <span v-else class="font-bold whitespace-nowrap">{{ p.name }}</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="trust-static">
                <div v-for="g in groups" :key="g.label" class="trust-col px-6 md:px-8">
                    <h3 class="trust-label">{{ g.label }}</h3>
                    <ul class="flex min-h-14 flex-wrap items-center gap-x-8 gap-y-4">
                        <li v-for="p in g.partners" :key="p.name">
                            <img v-if="p.logo" :src="p.logo" :alt="p.name" loading="lazy" class="trust-logo" />
                            <span v-else class="font-bold">{{ p.name }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.trust {
    --trust-line: #e2e8f0;
    background: #ffffff;
    color: var(--night);
    border-bottom: 1px solid var(--trust-line);
}
.trust-col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
/* Subtle dividers: under the organisers when stacked, beside them when side by side, and between moving groups */
.trust-org {
    border-bottom: 1px solid var(--trust-line);
}
.trust-group {
    border-right: 1px solid var(--trust-line);
}
.trust-label {
    font-family: var(--font-mono);
    font-size: 0.6875rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    white-space: nowrap;
    color: var(--green);
}
.trust-logo {
    height: 1.75rem;
    width: auto;
    max-width: 11rem;
    object-fit: contain;
}
/* Columns fade in and out at the edges instead of being cut */
.trust-marquee {
    mask-image: linear-gradient(to right, transparent, #000 5%, #000 95%, transparent);
}
/* Static columns: screen readers only, unless motion is reduced, where they replace the marquee */
.trust-static {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}
@media (min-width: 1024px) {
    .trust-org {
        border-bottom: 0;
        border-right: 1px solid var(--trust-line);
    }
}
@media (prefers-reduced-motion: reduce) {
    .trust-marquee {
        display: none;
    }
    .trust-static {
        position: static;
        width: auto;
        height: auto;
        overflow: visible;
        clip-path: none;
        display: flex;
        flex-wrap: wrap;
        row-gap: 1.5rem;
    }
}
</style>
