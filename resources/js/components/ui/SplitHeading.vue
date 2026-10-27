<script setup lang="ts">
import { computed } from 'vue';

// Renders words inside masks so useReveal can raise them line by line without touching Vue-owned DOM.
const props = withDefaults(defineProps<{ text: string; as?: string; emphasis?: string; emphasisClass?: string }>(), {
    as: 'h2',
    emphasis: '',
    emphasisClass: 't-outline',
});

const words = computed(() => {
    const start = props.emphasis ? props.text.toLowerCase().indexOf(props.emphasis.toLowerCase()) : -1;
    const end = start + props.emphasis.length;
    let pos = 0;
    return props.text.split(' ').map((w) => {
        const at = props.text.indexOf(w, pos);
        pos = at + w.length;
        return { w, em: start >= 0 && at >= start && at < end };
    });
});
</script>

<template>
    <component :is="as" data-reveal="lines" :aria-label="text">
        <template v-for="(word, i) in words" :key="i">
            <span class="line-mask" aria-hidden="true"><span class="line-inner" :class="word.em && emphasisClass">{{ word.w }}</span></span>{{ i < words.length - 1 ? ' ' : '' }}
        </template>
    </component>
</template>
