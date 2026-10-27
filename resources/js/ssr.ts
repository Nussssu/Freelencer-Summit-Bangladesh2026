import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { createSSRApp, h, type App } from 'vue';
import { makeI18n, type Locale } from './i18n';
import { resolvePage, title } from './inertia';

/**
 * Inertia v2.3 renders the page app into `innerHTML` and awaits only the outer wrapper app.
 * Vue 3.5.44 stringifies innerHTML, so the inner Promise became "[object Promise]".
 * This render returns a promise that stringifies to a placeholder; the outer result swaps it for the real HTML.
 */
function deferredRender() {
    const parts = new Map<string, Promise<string>>();
    return (app: App) => {
        const html = renderToString(app);
        const token = `<!--ssr-part-${parts.size}-->`;
        parts.set(token, html);
        const done = html.then(async (out) => {
            for (const [tk, part] of parts) if (out.includes(tk)) out = out.replace(tk, await part);
            return out;
        });
        return Object.assign(done, { toString: () => token });
    };
}

createServer((page) =>
    createInertiaApp({
        page,
        render: deferredRender() as unknown as typeof renderToString,
        title,
        resolve: resolvePage,
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(makeI18n(props.initialPage.props.locale as Locale));
        },
    }),
);
