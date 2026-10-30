<script setup lang="ts">
// Tiered logo wall on rounded cards (the landing's PartnersWall grouping, in the inner-page card style).
defineProps<{ tiers: { name: string; sponsors: { name: string; logo: string | null; href?: string }[] }[] }>();
</script>

<template>
    <div class="grid gap-5 md:grid-cols-2">
        <section v-for="tier in tiers.filter((x) => x.sponsors.length)" :key="tier.name" class="in-card is-solid in-pad" :class="tier.sponsors.length > 3 && 'md:col-span-2'" data-reveal="fade">
            <h3 class="in-meta flex items-center gap-4 before:h-px before:flex-1 before:bg-line-light after:h-px after:flex-1 after:bg-line-light">{{ tier.name }}</h3>
            <ul class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                <li v-for="s in tier.sponsors" :key="s.name" class="grid min-h-12 place-items-center">
                    <img v-if="s.logo" :src="s.logo" :alt="s.name" class="h-8 w-auto max-w-[70vw] md:h-9" loading="lazy" />
                    <span v-else class="text-center text-lg font-extrabold text-ink-2">{{ s.name }}</span>
                </li>
            </ul>
        </section>
    </div>
</template>
