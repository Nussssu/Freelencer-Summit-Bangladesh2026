<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import { useFocusTrap } from '@/composables/useFocusTrap';
import { pauseScroll, resumeScroll } from '@/composables/useLenis';

// Shared modal / right-side drawer: focus trap, Escape closes, scroll paused, focus returns to the opener.
const props = withDefaults(defineProps<{ label: string; variant?: 'drawer' | 'modal'; tone?: 'dark' | 'light' }>(), { variant: 'drawer', tone: 'dark' });
const open = defineModel<boolean>('open', { required: true });
const { t } = useI18n();
const panel = ref<HTMLElement>();
const close = () => (open.value = false);
useFocusTrap(panel, open, close);
watch(open, (v) => {
    document.documentElement.style.overflow = v ? 'hidden' : '';
    if (v) pauseScroll();
    else resumeScroll();
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-300" leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[75] bg-night/70" aria-hidden="true" @click="close" />
        </Transition>
        <Transition
            :enter-active-class="variant === 'drawer' ? 'transition-transform duration-[600ms] ease-out-expo' : 'transition duration-300 ease-out-expo'"
            :leave-active-class="variant === 'drawer' ? 'transition-transform duration-300 ease-in' : 'transition duration-200'"
            :enter-from-class="variant === 'drawer' ? 'translate-x-full' : 'opacity-0 translate-y-4'"
            :leave-to-class="variant === 'drawer' ? 'translate-x-full' : 'opacity-0 translate-y-4'"
        >
            <div
                v-if="open"
                ref="panel"
                role="dialog"
                aria-modal="true"
                :aria-label="label"
                data-lenis-prevent
                class="fixed z-[76] overflow-y-auto"
                :class="[
                    variant === 'drawer' ? 'inset-y-0 right-0 w-full max-w-lg border-l' : 'inset-x-4 top-1/2 mx-auto max-h-[90svh] max-w-2xl -translate-y-1/2 border',
                    tone === 'dark' ? 'grain border-line-dark bg-night text-paper' : 'on-light border-line-light',
                ]"
            >
                <div class="flex items-center justify-between gap-4 border-b p-4" :class="tone === 'dark' ? 'border-line-dark' : 'border-line-light'">
                    <slot name="title"><p class="t-label">{{ label }}</p></slot>
                    <button type="button" class="t-label flex min-h-11 shrink-0 items-center gap-2 px-2 transition-colors duration-300 hover:text-red" @click="close">
                        {{ t('sections.close') }} <Icon name="close" />
                    </button>
                </div>
                <div class="p-6"><slot /></div>
            </div>
        </Transition>
    </Teleport>
</template>
