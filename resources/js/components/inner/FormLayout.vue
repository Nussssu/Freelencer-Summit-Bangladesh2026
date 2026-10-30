<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Glyph, { type GlyphName } from './Glyph.vue';

/**
 * Form page body: the form in a white rounded panel (first on mobile, so the action is in the first screen) and a
 * sticky "what happens next" card beside it on desktop with ticked points and a help link.
 */
withDefaults(defineProps<{ title?: string; points?: string[]; help?: string; helpHref?: string; icon?: GlyphName; tone?: string }>(), {
    title: 'What happens next',
    points: () => [],
    help: 'Questions before you apply? Contact the summit team.',
    helpHref: '/contact',
    icon: 'check',
    tone: 'tone-teal',
});
</script>

<template>
    <section class="in-section pt-0!">
        <div class="container-site grid gap-6 lg:grid-cols-12 lg:gap-10">
            <div id="form" class="min-w-0 scroll-mt-[calc(var(--header-h)+1rem)] lg:order-2 lg:col-span-8"><slot /></div>
            <aside class="lg:order-1 lg:col-span-4">
                <div class="in-card is-washed in-pad lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]" :class="tone">
                    <span class="in-disc"><Glyph :name="icon" /></span>
                    <h2 class="in-h3 mt-5">{{ title }}</h2>
                    <ul v-if="points.length" class="in-ticks mt-5 text-ink-2">
                        <li v-for="p in points" :key="p">{{ p }}</li>
                    </ul>
                    <slot name="aside" />
                    <p class="mt-6 border-t border-night/8 pt-5 text-sm text-ink-2">
                        <Link :href="helpHref" class="font-semibold text-green underline-offset-4 hover:underline">{{ help }}</Link>
                    </p>
                </div>
            </aside>
        </div>
    </section>
</template>
