<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import { utm, withUtm } from '@/composables/useUtm';
import type { DirectoryRow, Journey, JourneyStep } from '@/types/content';

// Searchable activation directory (brief §15). Rows are flagged as sample data in app/Content/HomeContent.php.
const props = defineProps<{ directory: Journey['directory']; steps: JourneyStep[] }>();
const { t } = useI18n();
const types = ['division', 'district', 'campus'] as const;
const tab = ref<DirectoryRow['type']>('division');
const query = ref('');
const tabEls: HTMLButtonElement[] = [];

const rows = computed(() => {
    const q = query.value.trim().toLowerCase();
    return props.directory.rows.filter((r) => r.type === tab.value && (!q || `${r.name} ${r.location ?? ''}`.toLowerCase().includes(q)));
});
const hrefFor = { division: props.steps[2].href, district: props.steps[1].href, campus: props.steps[0].href };

function onTabKey(e: KeyboardEvent, i: number) {
    const d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
    if (!d) return;
    const n = (i + d + types.length) % types.length;
    tab.value = types[n];
    tabEls[n]?.focus();
}
</script>

<template>
    <div class="container-site py-[var(--section-y)]">
        <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-5">
                <h3 class="font-display text-4xl font-black uppercase md:text-5xl">{{ t('sections.directory') }}</h3>
                <p v-if="directory.sample" class="t-label mt-3 inline-block bg-gold px-2 py-1 text-night">{{ t('common.sample') }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:col-span-7 lg:justify-end">
                <div role="tablist" :aria-label="t('sections.directory')" class="flex border border-line-light">
                    <button
                        v-for="(type, i) in types"
                        :key="type"
                        :ref="(el) => (tabEls[i] = el as HTMLButtonElement)"
                        role="tab"
                        type="button"
                        :id="`dir-tab-${type}`"
                        :aria-selected="tab === type"
                        aria-controls="dir-panel"
                        :tabindex="tab === type ? 0 : -1"
                        class="t-label min-h-11 flex-1 px-4 transition-colors duration-300"
                        :class="tab === type ? 'bg-night text-paper' : 'hover:text-green'"
                        @click="tab = type"
                        @keydown="onTabKey($event, i)"
                    >
                        {{ t(`sections.tabs.${type}`) }}
                    </button>
                </div>
                <label class="relative flex items-center">
                    <span class="sr-only">{{ t('sections.search') }}</span>
                    <Icon name="search" class="pointer-events-none absolute left-3 text-ink-2" />
                    <input v-model="query" type="search" :placeholder="t('sections.search')" class="min-h-11 w-full rounded-[2px] border border-line-light bg-snow py-2 pr-3 pl-10 text-sm placeholder:text-ink-2 focus:border-green sm:w-64" />
                </label>
            </div>
        </div>

        <div id="dir-panel" role="tabpanel" :aria-labelledby="`dir-tab-${tab}`" class="relative mt-8 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="t-label text-ink-2">
                    <tr class="border-b border-line-light">
                        <th scope="col" class="py-3 pr-4 font-medium">{{ t(`sections.tabs.${tab}`) }}</th>
                        <th scope="col" class="py-3 pr-4 font-medium">{{ t('sections.cols.date') }}</th>
                        <th scope="col" class="py-3 pr-4 font-medium">{{ t('sections.cols.location') }}</th>
                        <th scope="col" class="py-3 pr-4 font-medium">{{ t('sections.cols.partner') }}</th>
                        <th scope="col" class="py-3 text-right font-medium"><span class="sr-only">{{ t('sections.cols.register') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.name" class="border-b border-line-light transition-colors duration-300 hover:bg-mist">
                        <th scope="row" class="py-4 pr-4 font-display text-xl font-black uppercase">{{ r.name }}</th>
                        <td class="py-4 pr-4 text-ink-2">{{ r.date ?? t('common.tba') }}</td>
                        <td class="py-4 pr-4">{{ r.location ?? t('common.tba') }}</td>
                        <td class="py-4 pr-4 text-ink-2">{{ r.partner ?? t('common.tba') }}</td>
                        <td class="py-4 text-right">
                            <Link :href="withUtm('/register', utm)" class="link-u t-label inline-flex min-h-11 items-center gap-2 text-green">{{ t('sections.cols.register') }} <Icon name="arrow" :size="14" /></Link>
                        </td>
                    </tr>
                    <tr v-if="!rows.length">
                        <td colspan="5" class="py-6 text-ink-2">—</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="mt-8 flex flex-wrap gap-x-8 gap-y-2">
            <li v-for="type in types" :key="type">
                <Link :href="hrefFor[type]" class="link-u t-label inline-flex min-h-11 items-center gap-2">{{ t(`sections.tabs.${type}`) }} <Icon name="arrow" :size="14" /></Link>
            </li>
        </ul>
    </div>
</template>
