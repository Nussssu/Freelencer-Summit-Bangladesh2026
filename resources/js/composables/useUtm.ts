import { shallowRef } from 'vue';

const KEY = 'fsb_utm';
const PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as const;

/** Filled after hydration only, so server and first client render match. */
export const utm = shallowRef<Record<string, string>>({});

export function captureUtm() {
    try {
        const fromUrl = Object.fromEntries(
            PARAMS.map((k) => [k, new URLSearchParams(location.search).get(k)]).filter(([, v]) => v),
        ) as Record<string, string>;
        const stored = JSON.parse(sessionStorage.getItem(KEY) || '{}');
        const merged = Object.keys(fromUrl).length ? fromUrl : stored;
        sessionStorage.setItem(KEY, JSON.stringify(merged));
        utm.value = merged;
    } catch {
        // storage blocked: links simply stay untagged
    }
}

/** Appends the session's utm parameters to an internal CTA link. */
export function withUtm(href: string, params = utm.value): string {
    const keys = Object.keys(params);
    if (!keys.length || /^(https?:|mailto:|tel:|#)/.test(href)) return href;
    const [path, hash = ''] = href.split('#');
    const sep = path.includes('?') ? '&' : '?';
    return path + sep + new URLSearchParams(params).toString() + (hash ? `#${hash}` : '');
}
