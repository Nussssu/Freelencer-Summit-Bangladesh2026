# Landing touchpoints

Shared files changed while building inner pages. Every change is additive and keeps the landing's behaviour.
Frozen paths (docs/inner-design-kit.md) must show no diff against tag `landing-frozen`.

| Phase | File | Change | Landing impact |
|-------|------|--------|----------------|
| N0 | resources/js/layouts/MainLayout.vue | `STAGE_PAGES` list; canvas `v-show` only on Home, ComingNext, GrandSummit, NationalJourney | none: Home and ComingNext still show it |
| N0 | resources/js/components/layout/SeoHead.vue | optional `robots` prop (meta robots) | none: prop unset on Home |
| N0 | resources/js/inertia.ts | page glob `./pages/*.vue` → `./pages/**/*.vue` (nested pages) | none: top-level pages resolve as before |
| N0 | resources/js/i18n/en.ts | `inner` namespace imported from `inner.en.ts` | none: new keys only |
| N0 | resources/js/i18n/bn.ts | NATIVE-REVIEW comment lists `inner.*` | none |
| N0 | routes/web.php | local-only `/_kit` + `/_kit/form` | none: not registered outside local |
| N0 | bootstrap/app.php | `honeypot` middleware alias; Pint moved the two web middleware to imports | none: same middleware, same order |
| N0 | app/Providers/AppServiceProvider.php | `forms` rate limiter (5/min, 30/h per IP) | none: landing enquiry keeps `throttle:5,1` |
| N0 | config/filesystems.php | `private` disk (storage/app/private/uploads, not served) | none |
| N0 | database/seeders/DatabaseSeeder.php | default "Test User" seed replaced by StructureSeeder (+ DemoSeeder in local) | none: landing reads no seeded data |
| N0 | database (new migration) | `exhibit_enquiries` gains nullable reference, consent, ip_hash, utm_source, deleted_at | none: landing popup still inserts the same fields |
| N1 | routes/web.php | main-nav paths (`InnerPageContent::PAGES`) routed to InnerPageController; the rest stay on PageController | none: same URLs, `/` untouched |
| N1 | components/layout/SiteHeader.vue | goes solid at the top over `[data-header=light]`; re-samples on Inertia navigate; active nav link (teal/gold underline + aria-current) | none on `/`: no light marker there, no nav link matches `/` |
| N1 | components/layout/MenuOverlay.vue | active item in teal with a dot + aria-current | none on `/` beyond Home marked current |
| N1 | i18n/inner.en.ts | `inner.list` day/type/hall/role/country | none |
