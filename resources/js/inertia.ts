import type { DefineComponent } from 'vue';
import MainLayout from './layouts/MainLayout.vue';

const SITE = 'Freelancer Summit Bangladesh';

export const title = (t: string) => (!t ? SITE : t.includes(SITE) ? t : `${t} · ${SITE}`);

const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');

// Every page shares the persistent MainLayout (header, footer, canvas, smooth scroll survive navigation).
export async function resolvePage(name: string) {
    const page = (await pages[`./pages/${name}.vue`]()).default;
    page.layout ??= MainLayout;
    return page;
}
