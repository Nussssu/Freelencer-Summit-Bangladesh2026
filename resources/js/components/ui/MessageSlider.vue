<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import { reducedMotion } from '@/composables/motion';

// Take Off rotating messages (T5): 800ms slide, arrows; 5s per message (brief) with segmented progress ticks.
const props = withDefaults(defineProps<{ messages: string[]; interval?: number }>(), { interval: 5000 });
const { t } = useI18n();
const index = ref(0);
const playing = ref(false);
const userMoved = ref(false);
let timer: ReturnType<typeof setInterval> | undefined;

const go = (d: number, byUser = false) => {
    index.value = (index.value + d + props.messages.length) % props.messages.length;
    if (byUser) (userMoved.value = true), restart();
};
const restart = () => {
    clearInterval(timer);
    if (playing.value) timer = setInterval(() => go(1), props.interval);
};
const setPlaying = (v: boolean) => ((playing.value = v), restart());

onMounted(() => setPlaying(!reducedMotion()));
onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div class="max-w-xl" @mouseenter="setPlaying(false)" @mouseleave="setPlaying(!reducedMotion())" @focusin="setPlaying(false)">
        <div class="overflow-hidden" :aria-live="userMoved ? 'polite' : 'off'" aria-roledescription="carousel">
            <div class="flex transition-transform duration-[800ms] ease-out-expo" :style="{ transform: `translateX(${-index * 100}%)` }">
                <p
                    v-for="(m, i) in messages"
                    :key="i"
                    class="w-full shrink-0 pr-6 text-xl leading-snug font-medium text-balance md:text-2xl"
                    :aria-hidden="i !== index"
                    aria-roledescription="slide"
                    :aria-label="`${i + 1} / ${messages.length}`"
                >
                    {{ m }}
                </p>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-4">
            <div class="flex flex-1 gap-1" aria-hidden="true">
                <span v-for="(_, i) in messages" :key="i" class="relative h-0.5 flex-1 overflow-hidden bg-line-dark">
                    <span
                        class="absolute inset-0 origin-left bg-gold"
                        :class="i < index ? 'scale-x-100' : i === index && playing ? 'tick-run' : i === index ? 'scale-x-100' : 'scale-x-0'"
                        :style="i === index && playing ? { animationDuration: `${interval}ms` } : undefined"
                        :key="`${i}-${index}-${playing}`"
                    />
                </span>
            </div>
            <button type="button" class="grid size-11 place-items-center border border-line-dark transition-colors duration-300 hover:border-gold hover:text-gold" :aria-label="t('common.previous')" @click="go(-1, true)"><Icon name="arrow-left" /></button>
            <button type="button" class="grid size-11 place-items-center border border-line-dark transition-colors duration-300 hover:border-gold hover:text-gold" :aria-label="t('common.next')" @click="go(1, true)"><Icon name="arrow" /></button>
        </div>
    </div>
</template>

<style scoped>
.tick-run {
    animation: tick linear forwards;
}
@keyframes tick {
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}
</style>
