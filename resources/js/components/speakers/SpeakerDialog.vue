<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useFocusTrap } from '@/composables/useFocusTrap';
import { pauseScroll, resumeScroll } from '@/composables/useLenis';
import type { SpeakerCard } from '@/types/inner';

// Speaker popup for the Speakers & Experts grid: just the person (portrait, role, name, designation, organisation,
// biography or a note until it is published, expertise) and a close button that is always in view.
// Same modal mechanics as ui/Dialog: focus trap, Escape closes, scroll paused, focus returns to the opener.
// Any speaker list fits: the inner-page cards and the landing/Grand Summit speakers, whose name may still be unrevealed.
// Without `pillClass` the role pill uses the Speakers page colours.
type Person = Pick<SpeakerCard, 'photo' | 'role' | 'designation' | 'organisation' | 'expertise'> & { name: string | null };
const props = withDefaults(defineProps<{ people: Person[]; pillClass?: (role: string | null) => string }>(), {
    pillClass: (role: string | null) =>
        (role && ({ 'Chief Guest': 'bg-gold text-night', 'Special Guest': 'bg-[#e8506b] text-white', Guest: 'bg-[#2f8fdc] text-white', Speaker: 'bg-[#7b5cd6] text-white' } as Record<string, string>)[role]) || 'bg-gold text-night',
});
const nameOf = (p: Person) => p.name ?? t('common.revealingSoon');
const index = defineModel<number | null>('index', { required: true });
const { t } = useI18n();

const open = computed(() => index.value !== null);
const person = computed(() => (index.value === null ? null : props.people[index.value]));
const close = () => (index.value = null);

const panel = ref<HTMLElement>();
const mounted = ref(false);
onMounted(() => (mounted.value = true));
useFocusTrap(panel, open, close);
watch(open, (v) => {
    document.documentElement.style.overflow = v ? 'hidden' : '';
    if (v) pauseScroll();
    else resumeScroll();
});
</script>

<template>
    <Teleport v-if="mounted" to="body">
        <Transition enter-active-class="transition-opacity duration-200" leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[75] bg-night/60" aria-hidden="true" @click="close" />
        </Transition>
        <Transition enter-active-class="transition duration-200" leave-active-class="transition duration-150" enter-from-class="opacity-0 translate-y-3" leave-to-class="opacity-0">
            <div
                v-if="person"
                ref="panel"
                role="dialog"
                aria-modal="true"
                :aria-label="nameOf(person)"
                data-lenis-prevent
                class="fixed inset-x-4 top-1/2 z-[76] mx-auto max-h-[90svh] max-w-3xl -translate-y-1/2 overflow-y-auto rounded-2xl bg-white text-night shadow-[0_30px_70px_-30px_rgb(7_20_17/0.55)]"
            >
                <button
                    type="button"
                    class="absolute top-3 right-3 z-10 grid size-10 place-items-center rounded-full bg-white text-night shadow-md transition-colors hover:bg-night hover:text-white"
                    :aria-label="t('sections.close')"
                    data-autofocus
                    @click="close"
                >
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                </button>

                <div class="grid sm:grid-cols-[15rem_1fr]">
                    <div class="aspect-[4/5] bg-green/10 sm:aspect-auto">
                        <img v-if="person.photo" :src="person.photo" :alt="nameOf(person)" class="h-full w-full object-cover object-top" />
                    </div>
                    <div class="p-6 sm:p-8">
                        <span v-if="person.role" class="inline-block rounded-full px-3 py-1 text-xs font-semibold" :class="pillClass(person.role)">{{ person.role }}</span>
                        <h2 class="mt-3 pr-8 text-2xl leading-tight font-extrabold md:text-3xl">{{ nameOf(person) }}</h2>
                        <p v-if="person.designation" class="mt-2 text-ink-2">{{ person.designation }}</p>
                        <p v-if="person.organisation" class="mt-1 font-bold text-green">{{ person.organisation }}</p>

                        <p class="in-body mt-5 border-t border-night/10 pt-5">Full biography is published with the speaker announcement.</p>

                        <ul v-if="person.expertise.length" class="mt-4 flex flex-wrap gap-2">
                            <li v-for="e in person.expertise" :key="e" class="in-pill">{{ e }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
