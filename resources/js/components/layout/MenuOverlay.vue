<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import { useFocusTrap } from '@/composables/useFocusTrap';
import { pauseScroll, resumeScroll } from '@/composables/useLenis';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';
import SocialLinks from './SocialLinks.vue';

// Full-screen MENU with GITEX-style grouped panels (G9), reinterpreted: all 16 items, 5 action tiles, contacts.
// Light theme: ivory ground, navy headings, teal hover, gold CTA tiles.
const open = defineModel<boolean>('open', { required: true });
const { site, summit } = useShared();
const { t } = useI18n();
const panel = ref<HTMLElement>();

const close = () => (open.value = false);
useFocusTrap(panel, open, close);

const groups = computed(() =>
    site.value.navGroups.map((g) => ({
        ...g,
        items: site.value.navigation.map((n, i) => ({ ...n, index: String(i + 1).padStart(2, '0') })).filter((n) => n.group === g.id),
    })),
);

watch(open, (v) => {
    document.documentElement.style.overflow = v ? 'hidden' : '';
    if (v) pauseScroll();
    else resumeScroll();
});
const offNav = router.on('navigate', close);
onBeforeUnmount(() => {
    offNav();
    document.documentElement.style.overflow = '';
});
</script>

<template>
    <Transition
        enter-active-class="transition-[clip-path] duration-700 ease-out-expo"
        leave-active-class="transition-[clip-path] duration-500 ease-out-expo"
        enter-from-class="[clip-path:circle(0%_at_calc(100%-56px)_56px)]"
        enter-to-class="[clip-path:circle(150%_at_calc(100%-56px)_56px)]"
        leave-from-class="[clip-path:circle(150%_at_calc(100%-56px)_56px)]"
        leave-to-class="[clip-path:circle(0%_at_calc(100%-56px)_56px)]"
    >
        <div v-if="open" ref="panel" role="dialog" aria-modal="true" :aria-label="t('header.menu')" class="on-light light-tokens fixed inset-0 z-[60] overflow-y-auto bg-paper text-night" data-lenis-prevent>
            <div class="container-site flex min-h-full flex-col py-6">
                <div class="flex items-center justify-between border-b border-line-dark pb-4">
                    <span class="t-label text-green">{{ summit.hashtag }}</span>
                    <button type="button" class="t-label flex min-h-11 items-center gap-2 px-2 transition-colors duration-300 hover:text-green" data-autofocus @click="close">
                        {{ t('header.close') }} <Icon name="close" />
                    </button>
                </div>

                <div class="grid flex-1 gap-8 py-8 lg:grid-cols-12">
                    <nav :aria-label="t('header.navSite')" class="grid gap-8 sm:grid-cols-2 lg:col-span-8">
                        <section v-for="g in groups" :key="g.id" class="border-t border-line-dark pt-4">
                            <h2 class="t-label mb-4 text-green">{{ g.label }}</h2>
                            <ul>
                                <li v-for="item in g.items" :key="item.href">
                                    <Link :href="item.href" class="group flex items-baseline gap-4 py-1 font-display text-3xl font-black text-night uppercase transition-colors duration-300 hover:text-green md:text-4xl">
                                        <span class="t-label w-6 shrink-0 text-ink-2 transition-colors duration-300 group-hover:text-green">{{ item.index }}</span>
                                        <span class="transition-transform duration-300 group-hover:translate-x-2">{{ item.label }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </section>
                    </nav>

                    <div class="flex flex-col gap-6 lg:col-span-4">
                        <ul class="grid grid-cols-2 gap-2">
                            <li v-for="(c, i) in site.ctas.primary" :key="c.id" :class="i === 0 && 'col-span-2'">
                                <Link :href="withUtm(c.href, utm)" class="flex min-h-24 flex-col justify-between bg-gold p-4 text-night transition-colors duration-300 hover:bg-green hover:text-snow">
                                    <Icon name="arrow" class="-rotate-45 self-end" />
                                    <span class="text-sm leading-tight font-bold uppercase">{{ c.id === 'register' ? t('cta.register') : c.label }}</span>
                                </Link>
                            </li>
                        </ul>
                        <div class="border-t border-line-dark pt-4 text-sm">
                            <p class="t-label mb-2 text-green">{{ t('header.contact') }}</p>
                            <Link href="/contact" class="link-u transition-colors duration-300 hover:text-green">{{ t('header.contactTeam') }}</Link>
                            <p class="mt-2 text-ink-2">{{ site.organisers.map((o) => o.short).join(' × ') }} · {{ summit.city }}, Bangladesh</p>
                        </div>
                        <SocialLinks class="-ml-3" />
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
