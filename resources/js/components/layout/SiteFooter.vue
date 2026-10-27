<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import { gsap, useGsap } from '@/composables/motion';
import { scrollToTarget } from '@/composables/useLenis';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';
import SocialLinks from './SocialLinks.vue';

// Section 17. GITEX giant cropped footer wordmark (G13) with slow parallax; contacts per journey (G13 "Let's Talk").
const { site, summit } = useShared();
const { t } = useI18n();
const root = ref<HTMLElement>();
const year = computed(() => summit.value.edition);

useGsap(root, (reduced) => {
    if (!reduced) gsap.fromTo('[data-wordmark]', { yPercent: 30 }, { yPercent: -6, ease: 'none', scrollTrigger: { trigger: root.value, start: 'top bottom', end: 'bottom bottom', scrub: true } });
});
</script>

<template>
    <footer ref="root" id="footer" data-section="17" class="on-light light-tokens relative z-[1] overflow-hidden border-t border-line-light bg-paper-2 pt-16 pb-24 md:pb-8">
        <div class="container-site grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="t-label text-green">{{ summit.hashtag }}</p>
                <p class="mt-4 max-w-sm text-sm text-ink-2">
                    {{ t('footer.organisers', { names: site.organisers.map((o) => `${o.name} (${o.short})`).join(' · ') }) }}
                </p>
                <ul class="mt-6 space-y-2">
                    <li v-for="o in site.organisers" :key="o.short" class="flex items-baseline gap-3">
                        <span class="font-display text-2xl font-black uppercase">{{ o.short }}</span>
                        <span class="t-label text-ink-2">{{ o.role }}</span>
                    </li>
                </ul>
                <SocialLinks class="-ml-3 mt-6" />
            </div>

            <nav :aria-label="t('header.getInvolved')" class="lg:col-span-2">
                <h2 class="t-label mb-4 text-gold-ink">{{ t('header.getInvolved') }}</h2>
                <ul class="space-y-2 text-sm">
                    <li v-for="c in site.ctas.primary" :key="c.id"><Link :href="withUtm(c.href, utm)" class="link-u">{{ c.id === 'register' ? t('cta.register') : c.label }}</Link></li>
                </ul>
            </nav>

            <nav :aria-label="t('footer.explore')" class="lg:col-span-3">
                <h2 class="t-label mb-4 text-gold-ink">{{ t('footer.explore') }}</h2>
                <ul class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <li v-for="n in site.navigation" :key="n.href"><Link :href="n.href" class="link-u">{{ n.label }}</Link></li>
                </ul>
            </nav>

            <div class="lg:col-span-3">
                <h2 class="t-label mb-4 text-gold-ink">{{ t('footer.connect') }}</h2>
                <dl class="space-y-2 text-sm">
                    <div v-for="c in site.contacts" :key="c.label" class="flex justify-between gap-4 border-b border-line-light pb-2">
                        <dt>{{ c.label }}</dt>
                        <dd class="text-ink-2">{{ c.value ?? t('common.tba') }}</dd>
                    </div>
                </dl>
                <h2 class="t-label mt-8 mb-3 text-gold-ink">{{ t('footer.important') }}</h2>
                <ul class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
                    <li v-for="l in site.importantLinks" :key="l.href"><Link :href="l.href" class="link-u">{{ l.label }}</Link></li>
                </ul>
            </div>
        </div>

        <div class="relative mt-16 select-none" aria-hidden="true">
            <p data-wordmark class="font-display leading-[0.78] font-black whitespace-nowrap text-night/[0.06] uppercase" style="font-size: 19vw; margin-left: -1vw">Freelancer Summit</p>
        </div>

        <div class="container-site mt-4 flex flex-col gap-4 border-t border-line-light pt-6 text-sm text-ink-2 md:flex-row md:items-center">
            <p>© {{ year }} {{ t('brand.title') }}</p>
            <ul class="flex gap-4">
                <li v-for="l in site.legal" :key="l.href">
                    <a v-if="l.href.endsWith('.xml')" :href="l.href" class="link-u">{{ l.label }}</a>
                    <Link v-else :href="l.href" class="link-u">{{ l.label }}</Link>
                </li>
            </ul>
            <button type="button" class="t-label flex min-h-11 items-center gap-2 text-night transition-colors duration-300 hover:text-green md:ml-auto" @click="scrollToTarget(0)">
                {{ t('common.backToTop') }} <Icon name="up" :size="16" />
            </button>
        </div>
    </footer>
</template>
