<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Countdown from '@/components/ui/Countdown.vue';
import Icon from '@/components/ui/Icon.vue';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';
import LocaleToggle from './LocaleToggle.vue';
import MenuOverlay from './MenuOverlay.vue';
import SocialLinks from './SocialLinks.vue';
import Wordmark from './Wordmark.vue';

// BPO header pattern (date/venue chips, organiser lockup, register button; B3) + GITEX always-visible CTAs and MENU (G3, G9).
const { summit, site } = useShared();
const { t } = useI18n();

const register = computed(() => site.value.ctas.primary.find((c) => c.id === 'register')!);
const nominate = computed(() => site.value.ctas.primary.find((c) => c.id === 'nominate')!);
const involved = computed(() => site.value.ctas.primary.filter((c) => ['sponsor', 'exhibit', 'forum'].includes(c.id)));

const solid = ref(false);
const tone = ref<{ bg: string; dark: boolean; vivid: boolean }>();
const menuOpen = ref(false);
const dropOpen = ref(false);
const drop = ref<HTMLElement>();
const header = ref<HTMLElement>();

// Past the hero, take the background of the section under the header; if the section itself is transparent,
// the first painted element under the header's left edge (section band, not its cards).
const luminance = (rgb: number[]) => rgb.slice(0, 3).reduce((sum, v, i) => ((v /= 255), sum + [0.2126, 0.7152, 0.0722][i] * (v <= 0.04045 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4)), 0);
const paint = (el: Element) => {
    const bg = getComputedStyle(el).backgroundColor;
    const rgb = bg.match(/[\d.]+/g)?.map(Number) ?? [];
    return (rgb[3] ?? 1) === 0 ? null : { bg, rgb };
};
let frame = 0;
const sample = () => {
    frame = 0;
    // A light hero (PageHero tone="light") has no dark top for the transparent header, so sample from the first pixel.
    solid.value = scrollY > innerHeight * 0.75 || !!document.querySelector('[data-header="light"]');
    if (!solid.value || !header.value) return;
    const under = document.elementsFromPoint(1, header.value.offsetHeight + 1).find((el) => !header.value!.contains(el));
    const section = under?.closest('[data-section]');
    let found = section && paint(section);
    for (let el = under ?? null; !found && el; el = el.parentElement) found = paint(el);
    // 0.17 is where paper text and night text give equal contrast; below it paper text reads better.
    if (found) tone.value = { bg: found.bg, dark: luminance(found.rgb) < 0.17, vivid: Math.max(...found.rgb.slice(0, 3)) - Math.min(...found.rgb.slice(0, 3)) > 120 };
};
const onScroll = () => (frame ||= requestAnimationFrame(sample));
const onDocClick = (e: MouseEvent) => drop.value && !drop.value.contains(e.target as Node) && (dropOpen.value = false);
const onDropKey = (e: KeyboardEvent) => e.key === 'Escape' && dropOpen.value && ((dropOpen.value = false), drop.value?.querySelector('button')?.focus());

// The header lives in the persistent layout, so re-sample when Inertia swaps the page under it.
let offNavigate: VoidFunction | undefined;
onMounted(() => {
    sample();
    addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', onDocClick);
    offNavigate = router.on('navigate', onScroll);
});
onBeforeUnmount(() => {
    offNavigate?.();
    removeEventListener('scroll', onScroll);
    cancelAnimationFrame(frame);
    document.removeEventListener('click', onDocClick);
});
</script>

<template>
    <header
        ref="header"
        class="fixed inset-x-0 top-0 z-50 transition-colors duration-300"
        :class="!solid ? 'bg-gradient-to-b from-night/85 to-transparent text-paper' : tone?.dark ? 'border-b border-line-dark text-paper' : ['on-light light-tokens border-b border-line-light', tone?.vivid && 'header-mono']"
        :style="solid && tone ? { backgroundColor: tone.bg } : undefined"
    >
        <!-- Utility bar -->
        <div class="hidden h-9 border-b border-line-dark md:block">
            <div class="container-site flex h-full items-center gap-4 xl:max-w-none xl:px-8">
                <span class="t-label border border-line-dark px-2 py-1">{{ t('header.lateNov', { edition: summit.edition }) }}</span>
                <span class="t-label border border-line-dark px-2 py-1">{{ summit.venue ?? t('header.city') }}</span>
                <span v-if="!summit.dateConfirmed" class="text-xs text-muted">{{ t('header.tbc') }}</span>
                <Countdown :target="summit.startsAt" compact class="ml-4 hidden text-mint lg:block 2xl:hidden" />
                <div class="ml-auto flex items-center gap-2">
                    <LocaleToggle />
                    <span class="h-4 w-px bg-line-dark" aria-hidden="true" />
                    <SocialLinks />
                </div>
            </div>
        </div>

        <!-- Main bar -->
        <div class="container-site flex h-16 items-center gap-4 md:h-[76px] xl:max-w-none xl:gap-6 xl:px-8">
            <Wordmark />
            <div class="hidden items-center gap-2 border-l border-line-dark pl-4 lg:flex">
                <span class="t-label max-w-[6rem] text-[0.625rem] leading-tight text-muted">{{ t('brand.organisedBy') }}</span>
                <span class="font-display text-lg font-black tracking-wide uppercase">
                    <span class="text-bacco-light">{{ site.organisers[0].short }}</span>
                    <span class="px-1 text-gold">×</span>
                    <span>{{ site.organisers[1].short }}</span>
                </span>
            </div>


            <div class="ml-auto flex items-center gap-2">
                <Countdown :target="summit.startsAt" compact class="mr-2 hidden text-mint 2xl:block" />
                <CtaLink :href="register.href" class="hidden !px-4 md:inline-flex">{{ t('cta.register') }}</CtaLink>
                <CtaLink :href="nominate.href" tone="outline" class="hidden !px-4 lg:inline-flex">{{ nominate.label }}</CtaLink>

                <div ref="drop" class="relative hidden lg:block" @keydown="onDropKey">
                    <button type="button" class="btn btn-ghost !px-4" :aria-expanded="dropOpen" aria-controls="get-involved" @click="dropOpen = !dropOpen">
                        {{ t('header.getInvolved') }}
                        <Icon name="chevron" :size="14" class="transition-transform duration-300" :class="dropOpen && 'rotate-180'" />
                    </button>
                    <ul v-show="dropOpen" id="get-involved" class="absolute top-full right-0 mt-2 w-72 on-light light-tokens border border-line-light p-2">
                        <li v-for="c in involved" :key="c.id">
                            <Link :href="withUtm(c.href, utm)" class="flex min-h-11 items-center justify-between px-3 py-2 text-sm transition-colors duration-300 hover:bg-paper-2 hover:text-gold" @click="dropOpen = false">
                                {{ c.label }} <Icon name="arrow" :size="14" />
                            </Link>
                        </li>
                    </ul>
                </div>

                <button type="button" class="t-label flex min-h-11 items-center gap-2 px-2 text-sm transition-colors duration-300 hover:text-gold" aria-haspopup="dialog" :aria-expanded="menuOpen" @click="menuOpen = true">
                    <span class="flex w-6 flex-col gap-2" aria-hidden="true"><span class="h-px bg-current" /><span class="h-px w-4 bg-current" /></span>
                    {{ t('header.menu') }}
                </button>
            </div>
        </div>
        <!-- Nav row: BPO's two-tier header (B3); the five inline links -->
        <nav :aria-label="t('header.navPrimary')" class="hidden h-10 border-t border-line-dark xl:block">
            <ul class="container-site flex h-full items-center gap-8 xl:max-w-none xl:px-8">
                <li v-for="link in site.headerLinks" :key="link.href">
                    <Link :href="link.href" class="link-u text-[0.8125rem] font-medium tracking-wide whitespace-nowrap uppercase transition-colors duration-300 hover:text-gold">{{ link.label }}</Link>
                </li>
            </ul>
        </nav>
    </header>

    <MenuOverlay v-model:open="menuOpen" />
</template>

