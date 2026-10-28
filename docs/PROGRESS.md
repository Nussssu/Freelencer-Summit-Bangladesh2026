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
| N0 Inner kit + data foundation | done | docs/inner-design-kit.md, image-map.md, landing-touchpoints.md; tag `landing-frozen`; kit components (components/inner, components/forms) previewed at `/_kit` (local); 33 tables, 12 enums, query classes, honeypot + `throttle:forms`, private disk, BD mobile rule, queued branded mail; Pest FoundationTest. QA /_kit @1440/390: 0 console errors, 0 overflow, canvas hidden, URL-synced filters/pagination, error summary focus, valid submit. Frozen diff: none |
| N1 Registration journeys + contact | todo | |
| N2 Awards, sponsorship, exhibition, forum | todo | |
| N3 Grand summit, agenda, speakers | todo | |
| N4 National journey pages | todo | |
| N5 Content and information pages | todo | |
| N6 Final audit | todo | |

## Media regeneration
- Fallback clips/posters: run app, open `/?stage=1` at 1280x720 and 720x1280, hide DOM, MediaRecorder the canvas composited on #050E0A (6s, VP9 1.2Mbps) → public/media/delta-flow{,-m}.webm + posters.
- Share card: scripts/og/template.html → public/og-image.png (1200x630).

## Inner pages (N-phases)
- Read docs/inner-design-kit.md first; reuse components/inner + components/forms; data via app/Queries.
- Submissions: model `use IsSubmission` + `REF_PREFIX`; create with `Model::submit($validated, $request)`; routes `->middleware(['honeypot', 'throttle:forms'])`; mobile `new BdMobile` then `BdMobile::normalize()`; files `Uploads::rules()` / `Uploads::store()`; mail `SubmissionReceived` (queued).
- Ref prefixes: REG, AWD, SPO, EXH (apply), FRM, PRG, AMB, CON. Consent wording: config/consent.php (draft).
- Demo data: `php artisan db:seed` (local adds DemoSeeder, all rows is_sample). Never `migrate:fresh` on the shared DB (real enquiries).
- End of phase: `git diff --stat landing-frozen -- <frozen paths>` must be empty; log shared-file edits in docs/landing-touchpoints.md.

## Resume notes
- Run: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate && npm run build && php artisan inertia:start-ssr & php artisan serve`
- Content lives in app/Content/*.php; config in config/summit.php; TS types in resources/js/types/content.ts.
- Geo data: scripts/geo/build_geo.py → resources/js/stage/geo.json (committed).
