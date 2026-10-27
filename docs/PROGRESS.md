# Progress

| Phase | Status | Notes |
|-------|--------|-------|
| 0 Brief | done | docs/decisions.md logs PDF vs checklist differences |
| 1 Reference audit | done | docs/reference-audit.md (screens in .playwright-mcp, gitignored) |
| 2 Design decisions | done | docs/design-decisions.md |
| 3 Foundation | done | Laravel 13 + Inertia v2 + Vue 3 + TS + Tailwind v4, SSR entry, content classes, 28 routes, enquiry endpoint, tests green (34) |
| 4 Header/Hero/Journey | done | Delta Flow scene (40k particles), pinned journey, SVG fallback, video/poster + OG image recorded via Playwright. QA: 0 console errors, 0 overflow @1440/390, hero 120fps (5s sample, 120Hz headless) |
| 5 Sections | done | All 17 sections SSR-rendered; Dialog Teleport client-only (hydration fix). QA: 0 console errors/hydration warnings, 0 overflow @1440/390 |
| 6 Audit | todo | |

## Media regeneration
- Fallback clips/posters: run app, open `/?stage=1` at 1280x720 and 720x1280, hide DOM, MediaRecorder the canvas composited on #050E0A (6s, VP9 1.2Mbps) → public/media/delta-flow{,-m}.webm + posters.
- Share card: scripts/og/template.html → public/og-image.png (1200x630).

## Resume notes
- Run: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate && npm run build && php artisan inertia:start-ssr & php artisan serve`
- Content lives in app/Content/*.php; config in config/summit.php; TS types in resources/js/types/content.ts.
- Geo data: scripts/geo/build_geo.py → resources/js/stage/geo.json (committed).
