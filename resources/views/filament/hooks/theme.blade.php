{{-- Admin theme: light blue canvas, deep navy sidebar with white / slate-50 text (16:1+ on the navy). --}}
<style>
    :root { --fs-canvas: #eef5f9; --fs-navy: #0f172a; --fs-white: #ffffff; --fs-light: #f8fafc; --fs-red-ink: #b3132a; }

    .fi-body, .fi-layout, .fi-main-ctn { background-color: var(--fs-canvas); }

    .fi-sidebar { background-color: var(--fs-navy); color: var(--fs-light); }
    .fi-sidebar-header { background-color: var(--fs-navy); box-shadow: none; border-color: rgba(248, 250, 252, .12); }
    .fi-sidebar .fi-sidebar-group-label,
    .fi-sidebar .fi-sidebar-group-btn,
    .fi-sidebar .fi-sidebar-group-collapse-btn { color: var(--fs-light); }
    .fi-sidebar .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item-icon { color: var(--fs-light); }
    .fi-sidebar .fi-sidebar-item-btn:hover,
    .fi-sidebar .fi-sidebar-item-btn:focus-visible { background-color: rgba(248, 250, 252, .08); }
    /* Active: white text, lifted surface and a white edge marker, so it reads without relying on colour alone. */
    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn { background-color: rgba(255, 255, 255, .14); box-shadow: inset 3px 0 0 var(--fs-white); }
    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label { color: var(--fs-white); font-weight: 600; }
    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-icon { color: var(--fs-white); }

    /* Scrolls, but without a visible track. */
    .fi-sidebar, .fi-sidebar-nav { scrollbar-width: none; -ms-overflow-style: none; }
    .fi-sidebar::-webkit-scrollbar, .fi-sidebar-nav::-webkit-scrollbar { display: none; }

    /* Desktop layout: full-height sidebar carrying the brand; the topbar starts beside it (mobile keeps Filament's own). */
    @media (min-width: 64rem) {
        /* Filament makes it sticky below the topbar; fixed lifts it to the top, and the content keeps its column. */
        .fi-sidebar { position: fixed !important; top: 0 !important; inset-inline-start: 0; height: 100dvh !important; z-index: 31; }
        .fi-main-ctn { margin-inline-start: var(--sidebar-width); }
        .fi-body-has-topbar .fi-sidebar-header { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: .25rem;
            height: auto; min-height: 5.5rem; padding: 1.25rem 1.5rem; background-color: var(--fs-navy); box-shadow: none; border-bottom: 1px solid rgba(248, 250, 252, .12); }
        .fi-topbar-ctn { margin-inline-start: var(--sidebar-width); }
        .fi-topbar-start { display: none; }
    }
    .fi-sidebar-header .fi-sidebar-header-logo-ctn { flex: none; width: 100%; }
    .fi-sidebar-header .fi-logo { color: var(--fs-white); font-size: 1.125rem; line-height: 1.3; justify-content: flex-start; text-align: start; margin: 0; }
    .fs-brand-caption { color: var(--fs-light); opacity: .7; font-size: .6875rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; }

    .fi-topbar { box-shadow: none; border-bottom: 1px solid #dbe5ec; }
    .fi-topbar-end { gap: .75rem; }
    .fs-visit { display: inline-flex; align-items: center; gap: .375rem; height: 2.375rem; padding: 0 1rem; border-radius: .5rem;
        background: var(--fs-navy); color: var(--fs-white); font-size: .875rem; font-weight: 600; white-space: nowrap; }
    .fs-visit:hover { background: #1e293b; }
    .fs-back { display: inline-flex; align-items: center; gap: .375rem; margin-bottom: .5rem; padding: .375rem; border-radius: .5rem;
        border: 1px solid rgba(15, 23, 42, .12); color: inherit; font-size: .8125rem; font-weight: 600; }
    /* Registrations tables: filters open in the same row as search (no separate Filters panel or heading). */
    .fs-inline-filters .fi-ta-header-ctn { display: flex; flex-wrap: wrap; align-items: flex-end; }
    .fs-inline-filters .fi-ta-header-ctn > :not(.fi-ta-filters-above-content-ctn):not(.fi-ta-header-toolbar) { flex: 1 1 100%; }
    .fs-inline-filters .fi-ta-filters-above-content-ctn { flex: 1 1 32rem; min-width: 0; border: 0; padding: .75rem 0 .75rem 1.5rem; }
    .fs-inline-filters .fi-ta-filters { padding: 0; gap: 0; }
    .fs-inline-filters .fi-ta-filters-header { display: none; }
    .fs-inline-filters .fi-ta-header-toolbar { flex: 0 1 auto; border-top: 0; border-bottom: 0; margin-left: auto; }
    .fs-inline-filters .fi-ta-filters .fi-fo-field-label-content { white-space: nowrap; }
    .fs-inline-filters .fi-ta-filters > .fi-sc { display: flex !important; flex-wrap: wrap; gap: .75rem; }
    .fs-inline-filters .fi-ta-filters > .fi-sc > * { flex: 1 1 6.5rem; min-width: 6.5rem; }
    .fs-inline-filters .fi-ta-filters > .fi-sc > :has(.fi-fo-date-time-picker) { flex: 0 0 auto; min-width: 0; }
    .fs-inline-filters .fi-ta-filters > .fi-sc > :has([aria-label="District: Search"]) { flex-basis: 9rem; min-width: 9rem; }
    .fs-inline-filters .fi-ta-header-toolbar .fi-ta-search-field { width: 11.5rem; }
    /* From / To: one line, small uppercase FROM and TO beside native date boxes (mm/dd/yyyy + calendar icon) */
    .fs-inline-filters .fi-ta-filters > .fi-sc > :has(.fs-date-from) { align-self: flex-end; }
    .fs-inline-filters :has(> * > .fs-date-from), .fs-inline-filters :has(> .fs-date-from) { display: flex !important; align-items: center; gap: .625rem; }
    .fs-inline-filters :has(> * > .fs-date-from) > * { flex: none; width: auto; }
    .fs-inline-filters .fs-date-from, .fs-inline-filters .fs-date-to { flex: none; display: flex !important; align-items: center; gap: .5rem; }
    .fs-inline-filters .fs-date-from::before, .fs-inline-filters .fs-date-to::before { font-size: .6875rem; font-weight: 600; letter-spacing: .08em; color: #9ca3af; flex: none; }
    .fs-inline-filters .fs-date-from::before { content: "FROM"; }
    .fs-inline-filters .fs-date-to::before { content: "TO"; }
    .fs-inline-filters .fs-date-from .fi-input-wrp, .fs-inline-filters .fs-date-to .fi-input-wrp { flex: none; width: 8.75rem; border-radius: .75rem; background: #f5f4f0; }
    .fs-inline-filters .fs-date-from input, .fs-inline-filters .fs-date-to input { background: transparent; color: #111827; }
    /* Filter dropdowns in the site theme: teal highlight instead of grey/blue */
    .fs-inline-filters .fi-select-input-option:hover,
    .fs-inline-filters .fi-select-input-option.fi-selected { background: #087f8c; color: #fff; }
    .fs-inline-filters .fi-select-input-option[aria-selected="true"] { background: rgba(8, 127, 140, .12); color: #087f8c; font-weight: 600; }
    .fs-inline-filters .fi-select-input-option[aria-selected="true"]:hover,
    .fs-inline-filters .fi-select-input-option[aria-selected="true"].fi-selected { background: #087f8c; color: #fff; }
    .fs-inline-filters .fi-ta-filters .fi-select-input-btn:focus-within, .fs-inline-filters .fi-ta-filters .fi-input-wrp:focus-within { border-color: #087f8c; box-shadow: 0 0 0 1px #087f8c; }
    .fs-inline-filters .fi-ta-header-ctn + * , .fs-inline-filters .fi-ta-header-ctn { border-bottom: 1px solid rgba(15, 23, 42, .08); }
    .fs-back:hover { background: rgba(15, 23, 42, .05); }
    .fs-back:focus-visible { outline: 2px solid var(--fs-navy); outline-offset: 2px; }
    .fs-visit:focus-visible { outline: 2px solid var(--fs-navy); outline-offset: 2px; }

    .fs-view-website { padding: .75rem 1rem 0; border-top: 1px solid rgba(248, 250, 252, .12); }
    .fs-view-website a { display: flex; width: 100%; align-items: center; justify-content: center; gap: .5rem; padding: .625rem 1rem;
        border-radius: .5rem; background: rgba(248, 250, 252, .08); color: var(--fs-white); font-weight: 600; font-size: .875rem; border: 1px solid rgba(248, 250, 252, .12); transition: background-color .15s; }
    .fs-view-website a:hover { background: rgba(248, 250, 252, .16); }
    .fs-view-website a:focus-visible { outline: 2px solid var(--fs-white); outline-offset: 2px; }

    .fs-logout { padding: .5rem 1rem 1rem; }
    .fs-logout button { display: flex; width: 100%; align-items: center; justify-content: center; gap: .5rem; padding: .625rem 1rem;
        border-radius: .5rem; background: var(--fs-red-ink); color: var(--fs-white); font-weight: 600; font-size: .875rem; }
    .fs-logout button:hover { background: #8f0f22; }
    .fs-logout button:focus-visible { outline: 2px solid var(--fs-white); outline-offset: 2px; }
</style>
