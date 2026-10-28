<script setup lang="ts">
// Generic detail page body: readable main column + sticky facts aside, then an optional related band.
defineProps<{ asideLabel?: string }>();
</script>

<template>
    <section class="on-light section">
        <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-16">
            <div class="min-w-0 lg:col-span-8"><slot /></div>
            <aside v-if="$slots.aside" class="lg:col-span-4" :aria-label="asideLabel">
                <div class="card space-y-5 p-6 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]"><slot name="aside" /></div>
            </aside>
        </div>
    </section>
    <section v-if="$slots.related" class="section border-t border-line-light bg-snow">
        <div class="container-site"><slot name="related" /></div>
    </section>
</template>
