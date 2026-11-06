<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/inner/Breadcrumbs.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { InnerPage } from '@/types/inner';

// Privacy Policy and Terms of Use: a plain text document like any standard legal page, no images. Title and intro come
// from the page hero, the date and every section from Pages › Privacy Policy / Terms in the CMS.
defineProps<{ page: InnerPage; content: { updated: string; sections: { h: string; p: string[] }[] } }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <article class="legal on-light" data-header="light">
        <div class="container-site max-w-4xl! pt-[calc(var(--header-h)+2rem)] pb-20 xl:pt-[calc(153px+2.5rem)]">
            <Breadcrumbs :items="page.crumbs" />
            <header class="mt-6 border-b border-night/10 pb-8">
                <h1 class="text-4xl font-extrabold tracking-tight text-night md:text-5xl">{{ page.hero.title }}</h1>
                <p v-if="page.hero.intro" class="mt-4 text-lg text-ink-2">{{ page.hero.intro }}</p>
                <p v-if="content.updated" class="mt-4 text-sm font-medium text-ink-2">Last updated: {{ content.updated }}</p>
            </header>

            <nav v-if="content.sections.length > 3" class="mt-8" aria-label="Contents">
                <p class="text-sm font-bold tracking-wide text-night uppercase">Contents</p>
                <ol class="mt-3 list-decimal space-y-1 pl-5 text-[0.9375rem] text-ink-2 sm:columns-2 sm:gap-10">
                    <li v-for="(s, i) in content.sections" :key="s.h"><a :href="`#s${i + 1}`" class="hover:text-green hover:underline">{{ s.h }}</a></li>
                </ol>
            </nav>

            <div class="in-prose mt-10 max-w-none">
                <section v-for="(s, i) in content.sections" :key="s.h">
                    <h2 :id="`s${i + 1}`" class="scroll-mt-[calc(var(--header-h)+1rem)]">{{ i + 1 }}. {{ s.h }}</h2>
                    <p v-for="p in s.p" :key="p">{{ p }}</p>
                </section>
            </div>

            <p class="mt-12 border-t border-night/10 pt-6 text-ink-2">
                Questions? <Link href="/contact" class="font-semibold text-green hover:underline">Contact the summit team</Link>.
            </p>
        </div>
    </article>
</template>

<style scoped>
.legal {
    background: var(--mist, #fff);
}
.legal :deep(.in-prose h2) {
    margin-top: 2.25em;
}
.legal :deep(.in-prose section:first-child h2) {
    margin-top: 0;
}
.legal :deep(.in-prose p) {
    margin-top: 0.9em;
}
</style>
