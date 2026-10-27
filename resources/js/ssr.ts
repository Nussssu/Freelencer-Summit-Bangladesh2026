import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { createSSRApp, h } from 'vue';
import { makeI18n, type Locale } from './i18n';
import { resolvePage, title } from './inertia';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title,
        resolve: resolvePage,
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(makeI18n(props.initialPage.props.locale as Locale));
        },
    }),
);
