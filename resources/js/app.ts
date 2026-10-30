import '../css/app.css';
import '../css/inner.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { createSSRApp, h } from 'vue';
import { makeI18n, type Locale } from './i18n';
import { resolvePage, title } from './inertia';

createInertiaApp({
    title,
    resolve: resolvePage,
    progress: false,
    setup({ el, App, props, plugin }) {
        const i18n = makeI18n(props.initialPage.props.locale as Locale);
        createSSRApp({ render: () => h(App, props) }).use(plugin).use(i18n).mount(el);
    },
});
