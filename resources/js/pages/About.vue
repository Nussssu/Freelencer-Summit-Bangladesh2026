<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import PageHero from '@/components/inner/PageHero.vue';
import Photo from '@/components/inner/Photo.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { AboutContent, InnerPage } from '@/types/inner';

// Brief §1–4: positioning, objectives, the four implementation strands, audiences, summit highlights, organisers.
defineProps<{ page: InnerPage; content: AboutContent }>();
const { site } = useShared();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <PageHero :eyebrow="page.hero.eyebrow" :title="page.hero.title" :emphasis="page.hero.emphasis" :intro="page.hero.intro" tone="light" variant="facts" :crumbs="page.crumbs">
        <template #actions>
            <CtaLink v-for="a in page.hero.actions" :key="a.href" :href="a.href" :tone="a.tone">{{ a.label }}</CtaLink>
        </template>
    </PageHero>

    <div ref="root" class="on-light">
        <!-- Why it exists: statement + three paragraphs, stamp -->
        <section class="section bg-snow" aria-labelledby="ab-why">
            <div class="container-site grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <SectionHead id="ab-why" :heading="content.headings.why" />
                    <p class="t-label mt-8 inline-block -rotate-2 border-2 border-red px-3 py-2 text-red" data-reveal="fade">{{ content.about.stamp }}</p>
                </div>
                <div class="lg:col-span-7">
                    <p class="font-display text-3xl leading-tight font-black uppercase md:text-4xl" data-reveal="fade">{{ content.about.statement }}</p>
                    <div class="stitch mt-8 max-w-xs" data-reveal="stitch" aria-hidden="true" />
                    <div class="mt-8 space-y-5 text-lg text-ink-2">
                        <p v-for="p in content.about.paragraphs" :key="p" data-reveal="fade">{{ p }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Strategic objectives (brief §3), numbered -->
        <section class="section" aria-labelledby="ab-objectives">
            <div class="container-site">
                <SectionHead id="ab-objectives" :heading="content.headings.objectives" />
                <ol class="mt-12 grid border-t border-line-light sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(o, i) in content.objectives" :key="o" class="flex gap-5 border-b border-line-light py-6 sm:pr-6 sm:odd:border-r lg:border-r lg:[&:nth-child(3n)]:border-r-0 lg:[&:not(:nth-child(3n+1))]:pl-6" data-reveal="fade">
                        <span class="font-display text-3xl leading-none font-black text-green">{{ String(i + 1).padStart(2, '0') }}</span>
                        <p class="text-lg">{{ o }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Four implementation strands (brief §4) -->
        <section class="section bg-mist" aria-labelledby="ab-phases">
            <div class="container-site">
                <SectionHead id="ab-phases" :heading="content.headings.phases" />
                <ol class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <li v-for="(p, i) in content.phases" :key="p.title" class="card card-hover group relative flex flex-col p-6" data-reveal="fade">
                        <p class="t-label text-ink-2">{{ String(i + 1).padStart(2, '0') }} · {{ p.coverage }}</p>
                        <h3 class="mt-4 font-display text-3xl leading-none font-black uppercase">
                            <Link :href="p.href" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ p.title }}</Link>
                        </h3>
                        <ul class="mt-5 flex-1 space-y-2 text-ink-2">
                            <li v-for="a in p.activities" :key="a" class="flex gap-2"><span class="mt-2.5 size-1.5 shrink-0 bg-green" aria-hidden="true" />{{ a }}</li>
                        </ul>
                        <Icon name="arrow" class="mt-6 text-green transition-transform duration-300 group-hover:translate-x-1" />
                    </li>
                </ol>
            </div>
        </section>

        <!-- Audiences (brief §6 "Why attend") -->
        <section class="section" aria-labelledby="ab-audiences">
            <div class="container-site">
                <SectionHead id="ab-audiences" :heading="content.headings.audiences" />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="a in content.audiences" :key="a.title" class="card flex flex-col p-6" data-reveal="fade">
                        <h3 class="text-xl font-semibold">{{ a.title }}</h3>
                        <ul class="mt-4 flex-1 space-y-2 text-ink-2">
                            <li v-for="b in a.benefits" :key="b" class="flex gap-2"><span class="mt-2.5 size-1.5 shrink-0 bg-gold" aria-hidden="true" />{{ b }}</li>
                        </ul>
                        <Link :href="a.href" class="link-u t-label mt-6 inline-flex min-h-11 items-center gap-2 self-start text-green">{{ a.cta }} <Icon name="arrow" :size="14" /></Link>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Summit highlights (brief §6) -->
        <section class="section bg-snow" aria-labelledby="ab-highlights">
            <div class="container-site">
                <SectionHead id="ab-highlights" :heading="content.headings.highlights" />
                <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="h in content.highlights" :key="h.title" class="card flex flex-col" data-reveal="clip">
                        <Photo :src="h.image" class="aspect-[16/10]" />
                        <div class="p-5">
                            <h3 class="text-xl font-semibold">{{ h.title }}</h3>
                            <p class="mt-2 text-ink-2">{{ h.text }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Organisers (brief cover: BACCO / DoICT) -->
        <section class="section" aria-labelledby="ab-organisers">
            <div class="container-site">
                <SectionHead id="ab-organisers" :heading="content.headings.organisers" />
                <ul class="mt-12 grid gap-6 md:grid-cols-2">
                    <li v-for="o in site.organisers" :key="o.short" class="card flex items-center gap-6 p-6" data-reveal="fade">
                        <span class="grid size-24 shrink-0 place-items-center border border-line-light font-display text-3xl font-black text-bacco">
                            <img v-if="o.logo" :src="o.logo" :alt="o.short" class="max-h-full w-auto p-2" />
                            <template v-else>{{ o.short }}</template>
                        </span>
                        <div>
                            <p class="t-label text-ink-2">{{ o.role }}</p>
                            <h3 class="mt-2 text-xl font-semibold">{{ o.name }}</h3>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <CtaLink v-for="c in site.ctas.primary" :key="c.id" :href="c.href" :tone="c.id === 'register' ? 'gold' : 'outline'">{{ c.label }}</CtaLink>
    </CtaBand>
</template>
