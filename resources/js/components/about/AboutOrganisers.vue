<script setup lang="ts">
import { computed, ref } from 'vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { AboutContent, SectionHeading } from '@/types/inner';
import SplitHeading from '@/components/ui/SplitHeading.vue';

// Organisers under a centred display heading (same size as News & Updates) as two white cards: logo field, hairline,
// short name over a one-line description.
// Logos and names come from Settings (site.organisers); the description from the page content, falling back to the name.
const props = defineProps<{ heading: SectionHeading; organisers: AboutContent['organisers'] }>();
const { site } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const cards = computed(() => site.value.organisers.map((o) => ({ ...o, text: props.organisers.find((c) => c.short === o.short)?.text ?? o.name })));
</script>

<template>
    <section ref="root" class="container-site" aria-labelledby="ab-organisers">
        <div class="border-t border-line-light py-12 md:py-14">
            <SplitHeading
                id="ab-organisers"
                :text="heading.title"
                :emphasis="heading.emphasis ?? undefined"
                emphasis-class="text-green"
                class="text-center font-display text-[clamp(2.75rem,6vw,5rem)] leading-[0.92] font-black text-night uppercase"
            />
            <ul class="mt-12 grid md:mt-16 gap-6 md:grid-cols-2">
                <li v-for="o in cards" :key="o.short" class="flex items-center rounded-xl bg-snow py-7 shadow-[0_18px_40px_-30px_rgb(7_20_17/0.35)]" data-reveal="fade">
                    <div class="grid w-36 shrink-0 place-items-center self-stretch border-r border-line-light px-5 sm:w-48 sm:px-8">
                        <img v-if="o.logo" :src="o.logo" :alt="o.name" class="max-h-14 w-auto" loading="lazy" />
                        <span v-else class="font-display text-4xl font-black text-bacco" translate="no">{{ o.short }}</span>
                    </div>
                    <div class="px-6 sm:px-8">
                        <h3 class="text-xl font-bold text-night" translate="no">{{ o.short }}</h3>
                        <p class="mt-1 max-w-[34ch] text-base leading-snug text-ink-2">{{ o.text }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
