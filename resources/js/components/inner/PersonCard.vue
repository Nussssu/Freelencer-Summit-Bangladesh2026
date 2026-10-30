<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SpeakerCard } from '@/types/inner';
import SampleRibbon from './SampleRibbon.vue';

// Speaker card: rounded portrait with the role as a pill, name, designation · organisation. Links to the profile.
defineProps<{ person: SpeakerCard; headingLevel?: 'h2' | 'h3' }>();
</script>

<template>
    <article class="in-card is-link is-solid group h-full overflow-hidden">
        <span class="in-media aspect-[4/5]">
            <img v-if="person.photo" :src="person.photo" alt="" loading="lazy" class="object-top" />
            <span v-if="person.role" class="absolute bottom-3 left-3 rounded-full bg-gold px-3 py-1 text-xs font-semibold text-night">{{ person.role }}</span>
            <SampleRibbon v-if="person.sample" class="absolute top-3 right-3" />
        </span>
        <div class="flex flex-1 flex-col p-5">
            <component :is="headingLevel ?? 'h3'" class="in-h3 text-xl!">
                <Link :href="person.href" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ person.name }}</Link>
            </component>
            <p v-if="person.designation || person.organisation" class="mt-1.5 text-[0.9375rem] leading-snug text-ink-2">{{ [person.designation, person.organisation].filter(Boolean).join(' · ') }}</p>
        </div>
    </article>
</template>
