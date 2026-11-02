<script setup lang="ts">
// Line icons for the tinted discs on inner-page cards (same stroke style as the landing's Get involved icons).
export type GlyphName =
    | 'user-plus' | 'user' | 'star' | 'handshake' | 'store' | 'people' | 'calendar' | 'pin' | 'mic' | 'ticket' | 'trophy' | 'doc' | 'pen' | 'map'
    | 'globe' | 'cap' | 'building' | 'briefcase' | 'rocket' | 'bulb' | 'chart' | 'chat' | 'mail' | 'phone' | 'shield' | 'check' | 'clock'
    | 'image' | 'play' | 'news' | 'layers' | 'ai' | 'target' | 'megaphone' | 'download' | 'link' | 'flag' | 'route' | 'id' | 'grid' | 'heart' | 'search';
defineProps<{ name: GlyphName }>();
</script>

<template>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <template v-if="name === 'user-plus'"><circle cx="10" cy="8" r="3.5" /><path d="M3.5 20a6.5 6.5 0 0 1 13 0M18.5 6v5M16 8.5h5" /></template>
        <template v-else-if="name === 'user'"><circle cx="12" cy="8" r="3.75" /><path d="M4.5 20a7.5 7.5 0 0 1 15 0" /></template>
        <template v-else-if="name === 'star'"><path d="m12 3.5 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8L12 3.5Z" /></template>
        <template v-else-if="name === 'handshake'"><path d="m11 17 2 2a1.4 1.4 0 0 0 2-2M13 15l2.5 2.5a1.4 1.4 0 0 0 2-2l-3-3M10 12.5l3.4-3.4a1.9 1.9 0 0 1 2.7 0L21 14M3 14l4.5-4.5a2 2 0 0 1 2.4-.3L12 10.5M3 9.5 6 6.5M21 9.5l-3-3M9 16l2 2a1.4 1.4 0 0 1-2 2l-2-2a1.4 1.4 0 0 1 2-2Z" /></template>
        <template v-else-if="name === 'store'"><path d="M4 9h16l-1.5-4.5h-13L4 9ZM4 9v1.5a2.7 2.7 0 0 0 5.3 0 2.7 2.7 0 0 0 5.4 0 2.7 2.7 0 0 0 5.3 0V9M5.5 13v6.5h13V13M9.5 19.5V16h5v3.5" /></template>
        <template v-else-if="name === 'people'"><circle cx="12" cy="7.5" r="3" /><circle cx="5.5" cy="9.5" r="2.2" /><circle cx="18.5" cy="9.5" r="2.2" /><path d="M6.5 19.5a5.5 5.5 0 0 1 11 0ZM2 18.5a4 4 0 0 1 4.6-4M22 18.5a4 4 0 0 0-4.6-4" /></template>
        <template v-else-if="name === 'calendar'"><rect x="3.5" y="5" width="17" height="15" rx="2.5" /><path d="M3.5 10h17M8 3v4M16 3v4M8 14h2M14 14h2M8 17h2" /></template>
        <template v-else-if="name === 'pin'"><path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11Z" /><circle cx="12" cy="10" r="2.4" /></template>
        <template v-else-if="name === 'mic'"><rect x="9" y="3" width="6" height="11" rx="3" /><path d="M5.5 11a6.5 6.5 0 0 0 13 0M12 17.5V21M8.5 21h7" /></template>
        <template v-else-if="name === 'ticket'"><path d="M3.5 8.5V6.5a1 1 0 0 1 1-1h15a1 1 0 0 1 1 1v2a2.5 2.5 0 0 0 0 5v2a1 1 0 0 1-1 1h-15a1 1 0 0 1-1-1v-2a2.5 2.5 0 0 0 0-5ZM14 6v12" stroke-dasharray="0" /></template>
        <template v-else-if="name === 'trophy'"><path d="M8 4h8v5a4 4 0 0 1-8 0V4ZM8 6H4.5a3 3 0 0 0 3.5 4M16 6h3.5a3 3 0 0 1-3.5 4M12 13v4M8 21h8M9.5 17h5" /></template>
        <template v-else-if="name === 'doc'"><path d="M6 3.5h8l4 4V20a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 6 20V3.5ZM14 3.5V8h4M9 12h6M9 15.5h6" /></template>
        <template v-else-if="name === 'pen'"><path d="M15.5 4.5l4 4L9 19l-5 1 1-5L15.5 4.5ZM13.5 6.5l4 4" /></template>
        <template v-else-if="name === 'map'"><path d="M9 4 3.5 6v14L9 18l6 2 5.5-2V4L15 6 9 4ZM9 4v14M15 6v14" /></template>
        <template v-else-if="name === 'globe'"><circle cx="12" cy="12" r="8.5" /><path d="M3.5 12h17M12 3.5c2.5 2.5 3.5 5.5 3.5 8.5s-1 6-3.5 8.5c-2.5-2.5-3.5-5.5-3.5-8.5s1-6 3.5-8.5Z" /></template>
        <template v-else-if="name === 'cap'"><path d="M2.5 9 12 4.5 21.5 9 12 13.5 2.5 9ZM6.5 11v4.5c1.5 1.5 3.5 2.2 5.5 2.2s4-.7 5.5-2.2V11M21.5 9v5" /></template>
        <template v-else-if="name === 'building'"><path d="M4 20.5V5.5l8-2.5v17.5M12 7h8v13.5M2.5 20.5h19M7 8h2M7 11.5h2M7 15h2M15 10.5h2M15 14h2" /></template>
        <template v-else-if="name === 'briefcase'"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12h18" /></template>
        <template v-else-if="name === 'rocket'"><path d="M14 4.5c3-1 5.5-1 5.5-1s0 2.5-1 5.5L13 14.5 9.5 11 15 5.5M9.5 11 6 10l2.5-3 4 .5M13 14.5l1 3.5 3-2.5-.5-4M7 17c-1.5.5-2.5 2.5-2.5 2.5s2-1 2.5-2.5Z" /></template>
        <template v-else-if="name === 'bulb'"><path d="M9 17.5h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2V17.5h5v-1.6c0-.8.4-1.5 1-2A6 6 0 0 0 12 3Z" /></template>
        <template v-else-if="name === 'chart'"><path d="M4 20.5h16M6.5 17v-5M11 17V8M15.5 17v-7M20 17V5" /></template>
        <template v-else-if="name === 'chat'"><path d="M4 5.5h16v10H9l-5 4v-14Z" /><path d="M8 9.5h8M8 12.5h5" /></template>
        <template v-else-if="name === 'mail'"><rect x="3" y="5.5" width="18" height="13" rx="2" /><path d="m3.5 7 8.5 6 8.5-6" /></template>
        <template v-else-if="name === 'phone'"><path d="M5 4h4l1.5 4.5-2.5 1.5a11 11 0 0 0 6 6l1.5-2.5L20 15v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1Z" /></template>
        <template v-else-if="name === 'shield'"><path d="M12 3 4.5 6v5.5c0 4.5 3.2 8 7.5 9.5 4.3-1.5 7.5-5 7.5-9.5V6L12 3Z" /><path d="m8.5 12 2.5 2.5 4.5-5" /></template>
        <template v-else-if="name === 'check'"><circle cx="12" cy="12" r="8.5" /><path d="m8 12.5 2.8 2.8L16.5 9.5" /></template>
        <template v-else-if="name === 'clock'"><circle cx="12" cy="12" r="8.5" /><path d="M12 7.5V12l3 2" /></template>
        <template v-else-if="name === 'image'"><rect x="3.5" y="4.5" width="17" height="15" rx="2.5" /><circle cx="9" cy="10" r="1.8" /><path d="m4 17.5 5-4.5 3.5 3 3-2.5 4.5 4" /></template>
        <template v-else-if="name === 'play'"><rect x="3" y="5" width="18" height="14" rx="3" /><path d="m10 9 5 3-5 3V9Z" /></template>
        <template v-else-if="name === 'news'"><path d="M4 5h13v14.5H5.5A1.5 1.5 0 0 1 4 18V5ZM17 9h3v9a1.5 1.5 0 0 1-3 0M7.5 8.5h6M7.5 12h6M7.5 15.5h4" /></template>
        <template v-else-if="name === 'layers'"><path d="M12 3.5 3 8.5l9 5 9-5-9-5ZM3 12.5l9 5 9-5M3 16.5l9 5 9-5" /></template>
        <template v-else-if="name === 'ai'"><rect x="6.5" y="6.5" width="11" height="11" rx="2" /><path d="M10 10h4v4h-4zM9.5 3v3.5M14.5 3v3.5M9.5 17.5V21M14.5 17.5V21M3 9.5h3.5M3 14.5h3.5M17.5 9.5H21M17.5 14.5H21" /></template>
        <template v-else-if="name === 'target'"><circle cx="12" cy="12" r="8.5" /><circle cx="12" cy="12" r="5" /><circle cx="12" cy="12" r="1.5" /></template>
        <template v-else-if="name === 'megaphone'"><path d="M4 10v4h3l8 4.5v-13L7 10H4ZM18 9.5a3.5 3.5 0 0 1 0 5M8 14l1.5 5.5h2.5L11 15" /></template>
        <template v-else-if="name === 'download'"><path d="M12 4v11M7 10.5l5 5 5-5M4.5 20h15" /></template>
        <template v-else-if="name === 'link'"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1" /></template>
        <template v-else-if="name === 'flag'"><path d="M5 21V4M5 4.5h11l-2 4 2 4H5" /></template>
        <template v-else-if="name === 'route'"><circle cx="6" cy="18" r="2.5" /><circle cx="18" cy="6" r="2.5" /><path d="M8.5 18H15a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h6.5" /></template>
        <template v-else-if="name === 'id'"><rect x="3" y="5" width="18" height="14" rx="2.5" /><circle cx="9" cy="11" r="2.2" /><path d="M5.5 16a3.5 3.5 0 0 1 7 0M14.5 10h4M14.5 13.5h3" /></template>
        <template v-else-if="name === 'grid'"><rect x="4" y="4" width="7" height="7" rx="1.5" /><rect x="13" y="4" width="7" height="7" rx="1.5" /><rect x="4" y="13" width="7" height="7" rx="1.5" /><rect x="13" y="13" width="7" height="7" rx="1.5" /></template>
        <template v-else-if="name === 'search'"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m15.5 15.5 5 5" /></template>
        <template v-else-if="name === 'heart'"><path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.2a4.3 4.3 0 0 1 7.5 2.6C19.5 15.4 12 20 12 20Z" /></template>
    </svg>
</template>
