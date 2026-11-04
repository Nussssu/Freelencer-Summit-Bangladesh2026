<script setup lang="ts">
// The lanyard pass on the agenda's closing CTA: a tilted teal badge with a metal clip, the ribbon looping behind it.
defineProps<{ edition: string }>();
</script>

<template>
    <svg viewBox="0 0 320 360" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="eb-ribbon" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#3cc9d2" />
                <stop offset="0.55" stop-color="#1ca3ae" />
                <stop offset="1" stop-color="#11848f" />
            </linearGradient>
            <linearGradient id="eb-ribbon-back" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#36c2cb" />
                <stop offset="0.6" stop-color="#1b9ea9" />
                <stop offset="1" stop-color="#147f8a" />
            </linearGradient>
            <linearGradient id="eb-card" x1="0" y1="0" x2="0.6" y2="1">
                <stop offset="0" stop-color="#0e7884" />
                <stop offset="1" stop-color="#075560" />
            </linearGradient>
            <linearGradient id="eb-edge" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#b9f1f4" />
                <stop offset="0.5" stop-color="#5fcbd3" />
                <stop offset="1" stop-color="#d6fafb" />
            </linearGradient>
            <linearGradient id="eb-wave" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#2bb3be" />
                <stop offset="1" stop-color="#8fe6ea" />
            </linearGradient>
            <linearGradient id="eb-metal" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#9aa6ab" />
                <stop offset="0.45" stop-color="#f4f7f8" />
                <stop offset="1" stop-color="#8b979c" />
            </linearGradient>
            <clipPath id="eb-clip"><rect x="34" y="104" width="196" height="236" rx="16" /></clipPath>
            <filter id="eb-shadow" x="-30%" y="-30%" width="160%" height="160%"><feDropShadow dx="0" dy="14" stdDeviation="14" flood-color="#03262b" flood-opacity="0.45" /></filter>
        </defs>

        <!-- Ribbon behind the badge: over the shoulder and looping back down on the right -->
        <path d="M168 -10 C 250 -10, 300 40, 298 120 C 296 200, 262 262, 222 262 C 190 262, 196 214, 230 196 C 262 180, 280 130, 262 92" stroke="#04363d" stroke-opacity="0.45" stroke-width="30" stroke-linecap="round" transform="translate(4 6)" />
        <path d="M168 -10 C 250 -10, 300 40, 298 120 C 296 200, 262 262, 222 262 C 190 262, 196 214, 230 196 C 262 180, 280 130, 262 92" stroke="url(#eb-ribbon-back)" stroke-width="26" stroke-linecap="round" />
        <path d="M168 -10 C 250 -10, 300 40, 298 120 C 296 200, 262 262, 222 262 C 190 262, 196 214, 230 196 C 262 180, 280 130, 262 92" stroke="#9eeaee" stroke-opacity="0.45" stroke-width="2.5" transform="translate(-10 -4)" />

        <g transform="rotate(6 132 222)" filter="url(#eb-shadow)">
            <!-- Badge -->
            <rect x="34" y="104" width="196" height="236" rx="16" fill="url(#eb-card)" />
            <g clip-path="url(#eb-clip)">
                <path d="M20 268 C 80 236, 150 250, 250 214 L 250 360 L 20 360 Z" fill="#0f8792" opacity="0.55" />
                <path d="M20 292 C 90 258, 170 286, 250 248 L 250 360 L 20 360 Z" fill="url(#eb-wave)" opacity="0.55" />
                <path d="M20 318 C 100 290, 160 314, 250 288 L 250 360 L 20 360 Z" fill="#c4f3f5" opacity="0.35" />
                <path d="M34 104 L 120 104 L 34 200 Z" fill="#fff" opacity="0.06" />
            </g>
            <rect x="35.5" y="105.5" width="193" height="233" rx="15" stroke="url(#eb-edge)" stroke-width="3" />
            <rect x="106" y="116" width="52" height="9" rx="4.5" fill="#063f47" />
            <text x="54" y="170" fill="#fff" font-family="var(--font-sans), system-ui, sans-serif" font-weight="800" font-size="23" letter-spacing="-0.3">
                <tspan x="54">Freelancer</tspan>
                <tspan x="54" dy="27">Summit</tspan>
                <tspan x="54" dy="27">Bangladesh</tspan>
                <tspan x="54" dy="30" fill="#f5c33b" font-size="26">{{ edition }}</tspan>
            </text>

            <!-- Metal clip -->
            <rect x="118" y="78" width="28" height="38" rx="5" fill="url(#eb-metal)" />
            <rect x="126" y="90" width="12" height="16" rx="3" fill="#56636a" />
            <rect x="112" y="72" width="40" height="11" rx="3" fill="url(#eb-metal)" />
        </g>

        <!-- Front strap: from the clip up and out of frame -->
        <path d="M138 78 L 164 -10" stroke="#04363d" stroke-opacity="0.4" stroke-width="28" />
        <path d="M134 78 L 160 -10" stroke="url(#eb-ribbon)" stroke-width="26" stroke-linecap="butt" />
        <path d="M126 76 L 151 -10" stroke="#5fd0d8" stroke-opacity="0.5" stroke-width="3" />
    </svg>
</template>
