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
