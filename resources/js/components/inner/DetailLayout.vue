<script setup lang="ts">
// Detail page body: readable main column + sticky facts card, then an optional related band.
defineProps<{ asideLabel?: string }>();
</script>

<template>
    <section class="in-section">
        <div class="container-site grid gap-8 lg:grid-cols-12 lg:gap-14">
            <div class="min-w-0 lg:col-span-8"><slot /></div>
            <aside v-if="$slots.aside" class="lg:col-span-4" :aria-label="asideLabel">
                <div class="in-card is-solid in-pad space-y-5 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]"><slot name="aside" /></div>
            </aside>
        </div>
    </section>
    <section v-if="$slots.related" class="in-section">
        <div class="container-site"><slot name="related" /></div>
    </section>
</template>
