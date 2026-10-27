# Progress

| Phase | Status | Notes |
|-------|--------|-------|
| 0 Brief | done | docs/decisions.md logs PDF vs checklist differences |
| 1 Reference audit | done | docs/reference-audit.md (screens in .playwright-mcp, gitignored) |
| 2 Design decisions | done | docs/design-decisions.md |
| 3 Foundation | done | Laravel 13 + Inertia v2 + Vue 3 + TS + Tailwind v4, SSR entry, content classes, 28 routes, enquiry endpoint, tests green (34) |
| 4 Header/Hero/Journey | todo | |
| 5 Sections | todo | |
| 6 Audit | todo | |

## Resume notes
- Run: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate && npm run build && php artisan inertia:start-ssr & php artisan serve`
- Content lives in app/Content/*.php; config in config/summit.php; TS types in resources/js/types/content.ts.
- Geo data: scripts/geo/build_geo.py → resources/js/stage/geo.json (committed).
