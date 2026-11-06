<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SpeakerCard } from '@/types/inner';

// Speaker card: rounded portrait with the role as a pill, name, designation · organisation. Links to the profile
// unless `plain`; `pillClass` recolours the role pill (default gold).
withDefaults(defineProps<{ person: SpeakerCard; headingLevel?: 'h2' | 'h3'; plain?: boolean; pillClass?: string }>(), { plain: false, pillClass: 'bg-gold text-night' });
</script>

<template>
    <article class="in-card is-solid group h-full overflow-hidden" :class="!plain && 'is-link'">
        <span class="in-media aspect-[4/5]">
            <img v-if="person.photo" :src="person.photo" alt="" loading="lazy" class="object-top" />
            <span v-if="person.role" class="absolute bottom-3 left-3 rounded-full px-3 py-1 text-xs font-semibold" :class="pillClass">{{ person.role }}</span>
        </span>
        <div class="flex flex-1 flex-col p-5">
            <component :is="headingLevel ?? 'h3'" class="in-h3 text-xl!">
                <template v-if="plain">{{ person.name }}</template>
                <Link v-else :href="person.href" class="transition-colors duration-300 after:absolute after:inset-0 group-hover:text-green">{{ person.name }}</Link>
            </component>
            <p v-if="person.designation || person.organisation" class="mt-1.5 text-[0.9375rem] leading-snug text-ink-2">{{ [person.designation, person.organisation].filter(Boolean).join(' · ') }}</p>
            <div v-if="$slots.default" class="mt-auto pt-4"><slot /></div>
        </div>
    </article>
</template>
