<script setup lang="ts">
import { computed, ref } from 'vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent } from '@/types/inner';

// The summit's name and the positioning prose straight under the hero, in the reference About page's structure
// (bposummit.org.bd/about-us): a centred two-tone name (teal, then the emphasis in a summit-gold gradient), then
// full-width paragraphs of equal weight.
const props = defineProps<{ intro: AboutContent['intro']; justify?: boolean }>();
const root = ref<HTMLElement>();
useReveal(root);

const parts = computed(() => {
    const { title, emphasis } = props.intro;
    const i = emphasis ? title.indexOf(emphasis) : -1;
    return i < 0 ? [title, ''] : [title.slice(0, i), title.slice(i)];
});
</script>

<template>
    <section ref="root" class="container-site" aria-labelledby="ab-intro">
        <div class="border-b border-line-light py-12 md:py-14">
            <h2 id="ab-intro" class="text-center text-[clamp(2.25rem,4.6vw,4rem)] leading-[1.1] font-extrabold tracking-[-0.02em] text-green [text-wrap:balance]" data-reveal="fade">
                {{ parts[0] }}<span v-if="parts[1]" class="tone">{{ parts[1] }}</span>
            </h2>
            <div class="mt-10 space-y-8 md:mt-12">
                <p v-for="(p, i) in intro.paragraphs" :key="i" class="text-lg leading-[1.8] text-night" :class="justify && 'text-justify hyphens-auto'" data-reveal="fade">{{ p }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Second tone of the name: summit gold, deepened at the start so it holds on the light ground */
.tone {
    background: linear-gradient(90deg, #b07a12, var(--gold));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    -webkit-box-decoration-break: clone;
    box-decoration-break: clone;
}
</style>
