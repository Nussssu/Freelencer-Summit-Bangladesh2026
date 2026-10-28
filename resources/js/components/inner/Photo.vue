<script setup lang="ts">
// The landing's image treatment: duotone placeholder behind every photo, so a missing file still reads as designed.
withDefaults(defineProps<{ src?: string | null; alt?: string; eager?: boolean; position?: string }>(), { src: null, alt: '', eager: false, position: 'center' });
</script>

<template>
    <div class="duotone relative overflow-hidden" :role="!src && alt ? 'img' : undefined" :aria-label="!src && alt ? alt : undefined">
        <img v-if="src" :src="src" :alt="alt" :loading="eager ? 'eager' : 'lazy'" :fetchpriority="eager ? 'high' : undefined" decoding="async" class="absolute inset-0 h-full w-full object-cover" :style="{ objectPosition: position }" />
        <slot />
    </div>
</template>
