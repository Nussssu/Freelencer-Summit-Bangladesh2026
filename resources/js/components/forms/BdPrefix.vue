<script setup lang="ts">
// The fixed Bangladesh dialling prefix inside a phone input: the flag, then +880, ruled off from the number. Pages place it
// absolutely at the input's left edge and pad the input past it (pl-[6.75rem]).
</script>

<template>
    <span class="pointer-events-none absolute top-1/2 left-4 flex -translate-y-1/2 items-center gap-2 border-r border-night/15 pr-2.5 font-semibold text-night">
        <svg class="h-4 w-6 shrink-0 rounded-[3px] shadow-[0_0_0_1px_rgb(7_20_17/0.08)]" viewBox="0 0 20 12" aria-hidden="true">
            <rect width="20" height="12" fill="#006a4e" />
            <circle cx="9" cy="6" r="4" fill="#f42a41" />
        </svg>
        +880
    </span>
</template>
