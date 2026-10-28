<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SpeakerCard } from '@/types/inner';
import Photo from './Photo.vue';
import SampleRibbon from './SampleRibbon.vue';

// BPO-style large photo card: role label, name, designation · organisation. Links to the profile.
defineProps<{ person: SpeakerCard; headingLevel?: 'h2' | 'h3' }>();
</script>

<template>
    <article class="card card-hover group relative flex h-full flex-col">
        <Photo :src="person.photo" class="aspect-[4/5]" position="top">
            <span v-if="person.role" class="t-label absolute bottom-0 left-0 bg-night px-3 py-1.5 text-paper">{{ person.role }}</span>
            <SampleRibbon v-if="person.sample" class="absolute top-3 right-3" />
        </Photo>
        <div class="flex flex-1 flex-col p-5">
            <component :is="headingLevel ?? 'h3'" class="font-display text-2xl leading-none font-black uppercase">
                <Link :href="person.href" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ person.name }}</Link>
            </component>
            <p v-if="person.designation || person.organisation" class="mt-2 text-ink-2">{{ [person.designation, person.organisation].filter(Boolean).join(' · ') }}</p>
        </div>
    </article>
</template>
