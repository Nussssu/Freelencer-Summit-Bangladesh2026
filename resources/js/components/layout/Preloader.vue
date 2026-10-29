<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toBanglaDigits } from '@/i18n';
import { gsap, startIntro } from '@/composables/motion';

// Client-only (layout mounts it after hydration, so SSR HTML holds the real hero). Once per session, ≤1.6s, skippable.
const emit = defineEmits<{ done: [] }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const count = ref('০');
let tl: gsap.core.Timeline;

onMounted(() => {
    const n = { v: 0 };
    tl = gsap
        .timeline({ onComplete: () => emit('done') })
        .to(n, { v: 100, duration: 1, ease: 'power2.inOut', onUpdate: () => (count.value = toBanglaDigits(Math.round(n.v))) })
        .fromTo('[data-pre-stitch]', { clipPath: 'inset(0 100% 0 0)' }, { clipPath: 'inset(0 0% 0 0)', duration: 1, ease: 'power2.inOut' }, 0)
        .call(startIntro, [], 1)
        .to('[data-curtain="top"]', { yPercent: -100, duration: 0.6, ease: 'expo.inOut' }, 1)
        .to('[data-curtain="bottom"]', { yPercent: 100, duration: 0.6, ease: 'expo.inOut' }, 1)
        .to('[data-pre-content]', { opacity: 0, duration: 0.2 }, 1);
});
const skip = () => (tl?.progress(1), startIntro());
const onKey = (e: KeyboardEvent) => e.key === 'Escape' && skip();
onMounted(() => addEventListener('keydown', onKey));
onBeforeUnmount(() => (removeEventListener('keydown', onKey), tl?.kill(), startIntro()));
</script>

<template>
    <div ref="root" class="fixed inset-0 z-[90]">
        <div data-curtain="top" class="absolute inset-x-0 top-0 h-1/2 bg-night" />
        <div data-curtain="bottom" class="absolute inset-x-0 bottom-0 h-1/2 bg-night" />
        <div data-pre-content class="absolute inset-0 grid place-items-center">
            <div class="w-64 text-center">
                <p class="font-bangla text-7xl font-bold text-gold tabular-nums" aria-hidden="true">{{ count }}</p>
                <div data-pre-stitch class="stitch mt-4" />
                <button type="button" class="t-label mt-6 min-h-11 px-4 text-muted transition-colors duration-300 hover:text-paper" @click="skip">{{ t('common.skip') }}</button>
            </div>
        </div>
    </div>
</template>
