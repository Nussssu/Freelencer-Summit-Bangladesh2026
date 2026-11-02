<script setup lang="ts">
// Small inline icon set (stroke icons, currentColor). No icon font, no emoji.
defineProps<{ name: 'arrow' | 'arrow-left' | 'plus' | 'close' | 'play' | 'calendar' | 'search' | 'chevron' | 'facebook' | 'linkedin' | 'youtube' | 'x' | 'download' | 'up' | 'pin' | 'map' | 'cap' | 'people'; size?: number }>();
</script>

<template>
    <svg :width="size ?? 18" :height="size ?? 18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="square" aria-hidden="true">
        <path v-if="name === 'arrow'" d="M4 12h15M13 6l6 6-6 6" />
        <path v-else-if="name === 'arrow-left'" d="M20 12H5M11 6l-6 6 6 6" />
        <path v-else-if="name === 'plus'" d="M12 4v16M4 12h16" />
        <path v-else-if="name === 'close'" d="M5 5l14 14M19 5 5 19" />
        <path v-else-if="name === 'play'" d="M8 5v14l11-7z" fill="currentColor" stroke="none" />
        <path v-else-if="name === 'calendar'" d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4" />
        <path v-else-if="name === 'search'" d="M10.5 17a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13zM15.5 15.5 20 20" />
        <path v-else-if="name === 'chevron'" d="M6 9l6 6 6-6" />
        <path v-else-if="name === 'download'" d="M12 4v11M7 10l5 5 5-5M4 20h16" />
        <path v-else-if="name === 'up'" d="M12 20V5M6 11l6-6 6 6" />
        <path v-else-if="name === 'pin'" d="M12 21s-6.5-5.8-6.5-11a6.5 6.5 0 0 1 13 0c0 5.2-6.5 11-6.5 11zM12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
        <path v-else-if="name === 'map'" d="M3 6l6-2 6 2 6-2v14l-6 2-6-2-6 2zM9 4v14M15 6v14" />
        <path v-else-if="name === 'cap'" d="M2 9l10-5 10 5-10 5zM6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5M22 9v6" />
        <path v-else-if="name === 'people'" d="M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M16 4.3a3.5 3.5 0 0 1 0 6.4M18 14.4c2.1.8 3.5 2.8 3.5 5.6" />
        <path v-else-if="name === 'facebook'" d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z" />
        <path v-else-if="name === 'linkedin'" d="M4 9h4v11H4zM6 4a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM10 9h4v2c.6-1.2 2-2 3.5-2C20 9 20 11.5 20 13.5V20h-4v-6c0-1.5-.5-2-1.5-2S13 12.6 13 14v6h-3z" />
        <path v-else-if="name === 'youtube'" d="M3 8c0-2 1-3 3-3h12c2 0 3 1 3 3v8c0 2-1 3-3 3H6c-2 0-3-1-3-3zM10 9v6l5-3z" />
        <path v-else-if="name === 'x'" d="M4 4l16 16M20 4 4 20" />
    </svg>
</template>
