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

    .fs-logout { padding: .75rem 1rem 1rem; border-top: 1px solid rgba(248, 250, 252, .12); }
    .fs-logout button { display: flex; width: 100%; align-items: center; justify-content: center; gap: .5rem; padding: .625rem 1rem;
        border-radius: .5rem; background: var(--fs-red-ink); color: var(--fs-white); font-weight: 600; font-size: .875rem; }
    .fs-logout button:hover { background: #8f0f22; }
    .fs-logout button:focus-visible { outline: 2px solid var(--fs-white); outline-offset: 2px; }
</style>
