<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { utm, withUtm } from '@/composables/useUtm';
import type { DirectoryRow, Journey } from '@/types/content';
import type { SectionHeading } from '@/types/inner';

// "Find a programme near you", laid out like the client mock-up: eyebrow, headline, three pill tabs (all eight
// divisions, then districts and campuses) with a search box, and one card of rows (photo, name + venue, date, partner,
// Register). Tabs follow the ARIA tabs pattern; the search matches name, venue and partner within the open tab.
const props = defineProps<{ heading: SectionHeading; directory: Journey['directory'] }>();
const root = ref<HTMLElement>();
useReveal(root);

const tabs = [
    { type: 'division', label: 'Divisions', icon: 'pin' },
    { type: 'district', label: 'Districts', icon: 'map' },
    { type: 'campus', label: 'Campuses', icon: 'cap' },
] as const;
const tab = ref<DirectoryRow['type']>('division');
const query = ref('');
const tabEls: HTMLButtonElement[] = [];

const rows = computed(() => {
    const q = query.value.trim().toLowerCase();
    return props.directory.rows.filter((r) => r.type === tab.value && (!q || `${r.name} ${r.location ?? ''} ${r.partner ?? ''}`.toLowerCase().includes(q)));
});

function onTabKey(e: KeyboardEvent, i: number) {
    const d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
    if (!d) return;
    const n = (i + d + tabs.length) % tabs.length;
    tab.value = tabs[n].type;
    tabEls[n]?.focus();
}
</script>

<template>
    <!-- No top padding: the division grid above already ends on one section's worth of space. -->
    <section ref="root" class="section pt-0!" aria-labelledby="nj-finder">
        <div class="container-site">
            <p v-if="heading.eyebrow" class="t-label flex items-center gap-4 text-green" data-reveal="fade"><span class="h-0.5 w-14 bg-green" aria-hidden="true" />{{ heading.eyebrow }}</p>
            <SplitHeading id="nj-finder" :text="heading.title" :emphasis="heading.emphasis ?? undefined" emphasis-class="text-green" class="in-h2" :class="heading.eyebrow && 'mt-4'" />
            <p v-if="heading.intro" class="mt-4 max-w-2xl text-lg text-ink-2" data-reveal="fade">{{ heading.intro }}</p>

            <div class="mt-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" data-reveal="fade">
                <div role="tablist" aria-label="Programme type" class="-mx-4 flex gap-3 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                    <button
                        v-for="(x, i) in tabs"
                        :key="x.type"
                        :ref="(el) => (tabEls[i] = el as HTMLButtonElement)"
                        role="tab"
                        type="button"
                        :id="`finder-tab-${x.type}`"
                        :aria-selected="tab === x.type"
                        aria-controls="finder-panel"
                        :tabindex="tab === x.type ? 0 : -1"
                        class="inline-flex min-h-12 shrink-0 items-center gap-3 rounded-full border px-6 font-semibold transition-colors duration-300"
                        :class="tab === x.type ? 'border-green bg-green text-snow' : 'border-line-light bg-paper/70 text-night hover:border-green hover:text-green'"
                        @click="tab = x.type"
                        @keydown="onTabKey($event, i)"
                    >
                        <Icon :name="x.icon" :size="20" /> {{ x.label }}
                    </button>
                </div>
                <label class="relative flex items-center">
                    <span class="sr-only">Search programmes</span>
                    <Icon name="search" :size="20" class="pointer-events-none absolute left-5 text-night" />
                    <input v-model="query" type="search" placeholder="Search programmes" class="min-h-12 w-full rounded-full border border-line-light bg-paper/80 py-2 pr-5 pl-13 text-sm placeholder:text-ink-2 focus:border-green focus:outline-none lg:w-96" />
                </label>
            </div>

            <div id="finder-panel" role="tabpanel" :aria-labelledby="`finder-tab-${tab}`" class="mt-8 overflow-hidden rounded-2xl border border-white/80 bg-paper/75 shadow-[0_24px_60px_-34px_rgb(8_58_64/0.35)] backdrop-blur-sm" data-reveal="fade">
                <ul v-if="rows.length">
                    <li
                        v-for="r in rows"
                        :key="r.name"
                        class="grid grid-cols-[6.5rem_minmax(0,1fr)] items-center gap-x-4 gap-y-3 border-b border-line-light p-3 transition-colors duration-300 last:border-b-0 hover:bg-snow/80 md:grid-cols-[10rem_minmax(0,1fr)_auto] lg:grid-cols-[15rem_minmax(0,1fr)_13rem_minmax(0,17rem)_auto] lg:gap-x-0 lg:py-1 lg:pl-0"
                    >
                        <Photo :src="r.image" :alt="r.name" class="finder-photo aspect-[4/3] w-full self-start rounded-xl md:aspect-[16/10] lg:aspect-auto lg:h-20 lg:self-center" />
                        <div class="min-w-0 lg:pr-6 lg:pl-8">
                            <h3 class="text-lg leading-tight font-bold text-night lg:text-xl">{{ r.name }}</h3>
                            <p class="mt-1 flex items-start gap-2 text-sm text-ink-2"><Icon name="pin" :size="16" class="mt-0.5 shrink-0 text-green" />{{ r.location ?? 'TBA' }}</p>
                            <p class="mt-1 flex items-center gap-2 text-sm text-night lg:hidden"><Icon name="calendar" :size="16" class="shrink-0 text-green" /><time>{{ r.date ?? 'TBA' }}</time></p>
                        </div>
                        <p class="hidden h-10 items-center gap-3 border-l border-line-light px-8 whitespace-nowrap text-night lg:flex"><Icon name="calendar" :size="20" class="shrink-0 text-green" /><time>{{ r.date ?? 'TBA' }}</time></p>
                        <p class="hidden h-10 items-center gap-3 border-l border-line-light px-8 text-night lg:flex"><Icon name="people" :size="22" class="shrink-0 text-green" /><span class="min-w-0">{{ r.partner ?? 'TBA' }}</span></p>
                        <Link
                            :href="withUtm('/register', utm)"
                            class="col-span-2 inline-flex min-h-11 items-center justify-center gap-3 rounded-full bg-green px-7 font-semibold text-snow transition-colors duration-300 hover:bg-night md:col-span-1 lg:mr-5"
                            :aria-label="`Register for ${r.name}`"
                        >
                            Register <Icon name="arrow" :size="18" />
                        </Link>
                    </li>
                </ul>
                <p v-else class="p-8 text-ink-2">No programmes match “{{ query }}”.</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* The mock-up's photo edge: square on the left, a soft diagonal cut on the right (desktop rows only). */
@media (min-width: 1024px) {
    .finder-photo {
        clip-path: polygon(0 0, 100% 0, 86% 100%, 0 100%);
        border-radius: 0.75rem 1.25rem 1.25rem 0.75rem;
    }
}
</style>
