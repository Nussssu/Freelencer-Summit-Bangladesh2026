<script setup lang="ts">
// CSS-only loop: content rendered twice, the track slides -50%. Paused on hover; frozen under reduced motion.
withDefaults(defineProps<{ items: string[]; duration?: number; reverse?: boolean; separator?: string }>(), { duration: 40, separator: '·' });
</script>

<template>
    <div class="overflow-hidden" aria-hidden="true">
        <div class="marquee" :style="{ '--marquee-dur': `${duration}s` }" :data-reverse="reverse || undefined">
            <template v-for="copy in 2" :key="copy">
                <span v-for="item in items" :key="`${copy}-${item}`" class="flex shrink-0 items-center">
                    <slot :item="item">{{ item }}</slot>
                    <span class="px-6 opacity-50">{{ separator }}</span>
                </span>
            </template>
        </div>
    </div>
</template>
