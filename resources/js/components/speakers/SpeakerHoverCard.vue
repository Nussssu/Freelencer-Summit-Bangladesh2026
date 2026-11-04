<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

// Speaker photo card shared by the landing section and the Speakers & Experts page. At rest: full photo, country tab,
// name block. On hover/focus the photo glides into the avatar circle of a light profile panel (bio, role and country pills).
// With `href` it links to the profile; without it, it acts as a button (the page handles the click).
type Person = { name: string | null; designation: string | null; organisation: string | null; country: string | null; role: string | null; photo: string | null; bio?: string | null; credential?: string | null };
defineProps<{ person: Person; href?: string }>();
const { t } = useI18n();
const nameOf = (p: Person) => p.name ?? t('common.revealingSoon');
</script>

<template>
    <component
        :is="href ? Link : 'div'"
        :href="href"
        :role="href ? undefined : 'button'"
        :tabindex="href ? undefined : 0"
        class="sp-card relative block aspect-[2/3] cursor-pointer overflow-hidden rounded-xl text-night"
        :aria-label="`${nameOf(person)}, ${[person.designation, person.organisation].filter(Boolean).join(', ')}`"
        @keydown.enter.space.self="!href && ($event.preventDefault(), ($event.currentTarget as HTMLElement).click())"
    >
        <!-- Hover face (light): profile panel underneath; the photo shrinks into its avatar slot -->
        <div class="sp-profile absolute inset-0 flex flex-col" aria-hidden="true">
            <div class="flex items-start gap-3">
                <span class="sp-avatar-slot shrink-0" />
                <div class="min-w-0">
                    <p class="text-sm font-bold">{{ nameOf(person) }}</p>
                    <p class="text-xs uppercase leading-snug text-ink-2 sm:text-sm">{{ person.designation ?? t('sections.announcement') }}</p>
                    <p v-if="person.organisation" class="sp-org text-xs font-black uppercase sm:text-sm">{{ person.organisation }}</p>
                </div>
            </div>
            <hr class="my-3 border-night/10 sm:my-4" />
            <p class="text-xs text-ink-2 sm:text-sm">› Who is {{ nameOf(person) }}?</p>
            <p class="sp-bio mt-2 text-sm leading-relaxed sm:text-base">{{ person.credential ?? person.bio ?? t('sections.bioSoon') }}</p>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="sp-chip">{{ person.role }}</span>
                <span v-if="person.country" class="sp-chip">{{ person.country }}</span>
            </div>
        </div>

        <!-- The one photo: fills the card, then glides into the avatar circle on hover -->
        <div class="sp-photo silhouette absolute z-10 overflow-hidden" aria-hidden="true"><img v-if="person.photo" :src="person.photo" alt="" loading="lazy" class="h-full w-full object-cover object-[50%_25%]" /></div>

        <!-- Default face overlays: country tab and name block over a bottom fade -->
        <div class="sp-face pointer-events-none absolute inset-0 z-20 text-white">
            <span v-if="person.country" class="sp-tab t-label absolute top-1/4 right-0 bg-gold text-night">{{ person.country }}</span>
            <div class="sp-fade absolute inset-x-0 bottom-0 flex flex-col gap-0.5 p-3 pt-16 sm:p-4 sm:pt-20">
                <h3 class="text-sm font-bold sm:text-base">{{ nameOf(person) }}</h3>
                <p class="text-xs uppercase text-white/75 sm:text-sm">{{ person.designation ?? t('sections.announcement') }}</p>
                <p v-if="person.organisation" class="text-xs font-black uppercase text-gold sm:text-base">{{ person.organisation }}</p>
            </div>
        </div>
    </component>
</template>

<style scoped>
.silhouette {
    background:
        radial-gradient(circle at 50% 38%, rgb(8 112 122 / 0.18) 0 14%, transparent 15%),
        radial-gradient(ellipse 34% 30% at 50% 95%, rgb(8 112 122 / 0.14) 0 98%, transparent 100%),
        linear-gradient(160deg, var(--mist), var(--paper-2));
}
.silhouette:not(:has(img)) {
    filter: blur(1.5px);
}
/* --pad / --av: panel padding and avatar size; the photo's hover position is derived from them */
.sp-card {
    --pad: 0.75rem;
    --av: 2.75rem;
    --ease: cubic-bezier(0.65, 0, 0.25, 1);
    background: var(--paper-2);
    box-shadow:
        0 0 0 1px rgb(7 20 17 / 0.06),
        0 24px 48px -30px rgb(7 20 17 / 0.45);
}
@media (min-width: 640px) {
    .sp-card {
        --pad: 1rem;
        --av: 3.5rem;
    }
}
.sp-profile {
    padding: var(--pad);
    opacity: 0;
    transform: translateY(0.75rem);
    transition:
        opacity 0.3s ease,
        transform 0.4s var(--ease);
}
.sp-avatar-slot {
    width: var(--av);
    height: var(--av);
}
.sp-photo {
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 0;
    transition:
        top 0.6s var(--ease),
        left 0.6s var(--ease),
        width 0.6s var(--ease),
        height 0.6s var(--ease),
        border-radius 0.6s var(--ease),
        box-shadow 0.6s var(--ease);
}
.sp-face {
    transition: opacity 0.25s ease;
}
.sp-fade {
    background: linear-gradient(to top, rgb(7 20 17 / 0.96) 0%, rgb(7 20 17 / 0.7) 55%, transparent 100%);
}
/* Country tab on the right edge: vertical label with a notched left side, like a bookmark */
.sp-tab {
    writing-mode: vertical-rl;
    padding: 0.9rem 0.45rem 0.9rem 0.65rem;
    clip-path: polygon(30% 0, 100% 0, 100% 100%, 30% 100%, 0 82%, 0 18%);
}
.sp-org {
    color: #b07a12;
}
.sp-bio {
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.sp-chip {
    border-radius: 0.25rem;
    background: rgb(8 127 140 / 0.12);
    padding: 0.2rem 0.45rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--green);
}
.sp-card:hover .sp-face,
.sp-card:focus-visible .sp-face {
    opacity: 0;
}
.sp-card:hover .sp-photo,
.sp-card:focus-visible .sp-photo {
    top: var(--pad);
    left: var(--pad);
    width: var(--av);
    height: var(--av);
    border-radius: 50%;
    box-shadow: 0 0 0 2px var(--paper-2), 0 0 0 3px rgb(8 127 140 / 0.35);
}
/* Panel content follows the photo in, once it has mostly shrunk */
.sp-card:hover .sp-profile,
.sp-card:focus-visible .sp-profile {
    opacity: 1;
    transform: none;
    transition-delay: 0.2s;
}
@media (prefers-reduced-motion: reduce) {
    .sp-photo,
    .sp-profile {
        transition: opacity 0.2s ease;
        transform: none !important;
    }
}
</style>
