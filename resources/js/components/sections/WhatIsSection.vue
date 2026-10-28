<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { About } from '@/types/content';

// Section 3. Positioning statement with a scroll-driven marker highlight; narrow column; rotated stamp.
const props = defineProps<{ about: About }>();
const { summit } = useShared();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const parts = computed(() => {
    const i = props.about.statement.indexOf(props.about.highlight);
    return i < 0 ? [props.about.statement, '', ''] : [props.about.statement.slice(0, i), props.about.highlight, props.about.statement.slice(i + props.about.highlight.length)];
});

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-marker]', { backgroundSize: '0% 100%' }, { backgroundSize: '100% 100%', ease: 'none', scrollTrigger: { trigger: '[data-marker]', start: 'top 80%', end: 'bottom 45%', scrub: true } });
});
</script>

<template>
    <section ref="root" data-section="3" aria-labelledby="whatis-title" class="on-light flow section overflow-hidden">
        <div class="container-site">
            <h2 id="whatis-title" class="t-label text-green">{{ t('sections.whatIs', { name: summit.name, edition: summit.edition }) }}</h2>
            <p class="mt-6 max-w-6xl font-display text-[clamp(2.5rem,6vw,5.6rem)] leading-[0.95] font-black uppercase" data-reveal="fade">
                {{ parts[0] }}<mark data-marker class="marker">{{ parts[1] }}</mark>{{ parts[2] }}
            </p>
            <div class="stitch mt-10 max-w-xs" data-reveal="stitch" />

            <div class="relative mt-12 grid grid-cols-1 gap-10 lg:grid-cols-12">
                <div class="min-w-0 space-y-5 text-lg leading-relaxed lg:col-span-5 lg:col-start-6">
                    <p v-for="(p, i) in about.paragraphs" :key="i" data-reveal="fade"><span class="t-label mr-2 text-green">0{{ i + 1 }}</span>{{ p }}</p>
                </div>
                <p class="stamp t-label self-start justify-self-start lg:col-span-3 lg:col-start-10 lg:justify-self-end" data-reveal="fade">{{ about.stamp }}</p>
            </div>

            <div v-if="about.baccoStats" class="mt-16 border-t border-line-light pt-6">
                <dl class="grid grid-cols-2 gap-6 md:grid-cols-4">
                    <div v-for="s in about.baccoStats" :key="s.label" class="flex flex-col-reverse">
                        <dt class="mt-2 text-sm text-ink-2">{{ s.label }}</dt>
                        <dd class="t-stat">{{ s.value }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-ink-2">{{ about.baccoStatsSource }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.marker {
    color: inherit;
    background: linear-gradient(var(--gold), var(--gold)) no-repeat 0 85% / 100% 100%;
    padding-inline: 0.06em;
    box-decoration-break: clone;
    -webkit-box-decoration-break: clone;
}
.stamp {
    border: 2px solid var(--red);
    color: var(--red);
    padding: 12px 16px;
    font-size: 1.25rem; /* large text: red on paper is 3.4:1 */
    font-weight: 700;
    transform: rotate(-8deg);
    outline: 1px dashed var(--red);
    outline-offset: 4px;
}
</style>
