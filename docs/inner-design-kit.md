# Inner design kit

Read this first. Source of truth is the landing at `/` (tag `landing-frozen`). Values below are what the code actually uses.

## Tokens (resources/css/app.css, `:root` + `@theme inline`)
- Ink/surfaces: `night #071411` (text on light, dark bands), `delta #083a40` (deep teal band), `paper #f5f2e9` (default page), `snow #fff` (cards, inputs), `mist #e7f2f5` (soft band), `soft-green` (gradient band, awards).
- Accents: `green #087f8c` teal (links, focus on light, `btn-green`), `gold #eab94b` (primary CTA only), `red #f42a41` (one loud CTA or "Confirmed"), `mint #7fe0c8` (focus on dark, "Available"), `bacco #3167ae` (forum/organiser only). `gold-ink` = teal text on light.
- Text: `ink-2 #3f4b44` secondary on paper; `muted` on dark only; error text `red-ink`.
- Lines: `line-light` (10% night) on paper, `line-dark` (14% paper) on dark. Hairline 1px everywhere.
- Fonts: display Big Shoulders 900 UPPERCASE (`t-h1`, `t-h2`, `t-h2-xl`); UI/body Host Grotesk (16px/1.55); mono JetBrains (`t-label`, 12px, 0.08em, uppercase) for metadata only; Bangla Anek Bangla (auto via `html[lang=bn]`, display drops to 700, lh 1.12).
- Scale: h1 clamp(4.25rem,12.5vw,12rem) (landing hero only); inner page h1 uses `t-h2-xl` or `t-h2`; h3 = `text-xl font-semibold`; body `text-lg` for intros.
- Layout: `container-site` (1320px, 16px / 32px gutters), 12-col grid gap 24px (`gap-6`), `section` = padding-block clamp(4rem,7vw,7.5rem).
- Shape: radius 0 on cards/buttons/tags; inputs 6px (`rounded-md`, enquiry form). Cards = `.card` (white, hairline, soft shadow) + `.card-hover` (lift 3px, teal border). No other shadows.
- Buttons: `.btn` (gold, notched top-right 10px, hover → red) + `btn-green|btn-red|btn-blue|btn-outline|btn-ghost`; links with arrow via `<CtaLink>`. Inside forms buttons drop the notch (`[clip-path:none]`).
- Tags: `t-label border px-2 py-1` (teal border on light: see agenda type tags); filter chips = `t-label border min-h-11 px-4`, active = `bg-night text-paper`.
- Ornaments: `.stitch` (kantha gold dashes, divider/underline, `data-reveal="stitch"`), `.slot` (dashed placeholder), `.duotone` (image placeholder), `.grain` (dark bands only), `.grid-lines` (dark bands only).
- Rhythm: dark hero → paper → white/mist band → paper …; one dark or soft-green band per page max below the hero; final CTA band.

## Motion (composables/motion.ts, useReveal.ts, useLenis.ts)
- Lenis lerp 0.085 on the GSAP ticker; `pauseScroll/resumeScroll` for modals; `scrollToTarget(el)` offsets -96px.
- `useReveal(root)` + attributes: `lines` (SplitHeading words rise), `fade` (24px rise), `clip` (wipe from top), `stitch` (sew-in). Once, start `top 88%`, expo.out 0.9s, stagger 55ms. Hidden states only under JS, so SSR stays visible.
- Hover: 0.3s ease-in-out colour swap; cards lift 3px. Accordion: grid-rows 0fr→1fr 0.3s. Drawer: translateX 0.6s expo-out. Route wipe 0.45s.
- Reduced motion: CSS kills transitions/animations; useGsap passes `reduced` (skip setup); Lenis not started; stage off.

## Layout, stage, data
- `layouts/MainLayout.vue` is persistent (header, footer, Lenis, cursor, stage, mobile CTA bar, route wipe).
- Stage: `DeltaStage` (3D canvas) is no longer mounted (removed from MainLayout 2026-10-28); code kept in `stage/`. Full motion inventory: `/animation.md`.
- Header: transparent with a night gradient and paper text until 75vh, then samples the band under it. A light hero (`<PageHero tone="light">`, marked `data-header="light"`) makes it sample from the top instead. Main nav pages (About, National Journey, Agenda, Speakers, Awards) use the light hero; copy in `App\Content\InnerPageContent`, rendered by `InnerPageController`.
- Data: PHP content classes / Query classes → `Inertia::render('Page', props)` → typed props (`types/content.ts`, inner types in `types/inner.ts`). Shared props via `HandleInertiaRequests` (`summit`, `site`, `flash`, `locale`), read with `useShared()`. No query logic or copy in Vue.
- SEO: `<SeoHead title description path image? robots?>`; JSON-LD built in PHP (`App\Support\Seo`) and passed with `->withViewData(['jsonLd' => …])` (blade prints it).

## Reuse (components)
- layout: SiteHeader, MenuOverlay, SiteFooter, MobileCtaBar, SeoHead, SocialLinks, Wordmark.
- ui: CtaLink, Dialog (drawer | modal, light|dark, focus trap), Accordion (FAQ), SplitHeading, Countdown, RollingNumber (counters), Marquee, Icon, EditionStamp, MessageSlider.
- inner kit (components/inner): PageHero, Breadcrumbs, SubNav, CtaBand, TbcNotice, EmptyState, SampleRibbon, StatusBadge, DownloadCard, ShareButton, Photo, ListLayout, DetailLayout, InfoCard, PersonCard, TierCard, Pagination, SearchField, FilterChips, Skeleton.
- forms (components/forms): Field, TextField, SelectField, ChipsField, RadioCards, TextareaField, LinksField, FileField, ConsentField, StepShell, ErrorSummary, FormSuccess; `inner/useDirectory.ts` (URL-synced filters), `forms/useFormDraft.ts` (sessionStorage drafts).
- Preview of the whole kit: `/_kit` (local env only).

## Frozen paths (diff against `landing-frozen` every phase)
`resources/js/pages/Home.vue`, `resources/js/components/sections/**`, `resources/js/composables/**`, `resources/js/stage/**`, `resources/css/app.css`, `app/Content/HomeContent.php`, `app/Http/Controllers/HomeController.php`, `app/Http/Controllers/ExhibitEnquiryController.php`.
Shared, additive only (log in docs/landing-touchpoints.md): `routes/web.php`, `HandleInertiaRequests.php`, `i18n/*.ts`, `MainLayout.vue`, `SeoHead.vue`, `SiteContent.php`, `PageContent.php`, `types/content.ts`, `config/*.php`, `AppServiceProvider.php`.

## Inner-page rules (do / don't)
- DO open with the dark compact hero, breadcrumbs, one sentence of plain intro, then the action (form, list, CTA) within the first screen.
- DO put forms, directories, long text and policies on `paper` or `snow` cards with generous space; the canvas is hidden there.
- DO use photography from public/media through `<Photo>` (duotone fallback); real faces only from the supplied speaker photos.
- DO show honest states: TbcNotice, EmptyState ("Announcing soon", "Revealing soon"), SampleRibbon on demo rows, "Provisional" on tiers.
- DO keep copy warm and specific: "Register to attend", "We'll email your reference number", "Booths open once the floor plan is final".
- DO keep motion calm: fade/clip reveals, hover colour swaps, tab and filter transitions, accordions, drawers, a sticky SubNav.
- DON'T use the 3D canvas or maps except on /national-journey and /grand-summit heroes.
- DON'T use HUD/terminal styling, coordinates, scanlines, glitch, neon glow, wireframes, fake data widgets, or words like initialising, system, mission, protocol, node, transmission.
- DON'T use mono for body text, buttons or headings; mono is for dates, reference numbers and status tags.
- DON'T add new colours, radii or shadows; DON'T put gold on more than one CTA per view.
- DON'T invent people, numbers, sponsors, dates, venues, prices or legal text.

## Inner-page patterns to adopt (references, viewed 2026-10-28)
- BPO Summit /speakers: compact solid colour title band (~300px), then a 3-up grid of large photo cards with name + designation; detail on click. Adopt: 3-up PersonCard grid, role label, drawer or profile page.
- BPO Summit /sponsor: one section per tier ("Platinum Sponsors", "Gold Sponsors" …) with logos underneath. Adopt: tier band headers (same hairline-flanked labels as the landing PartnersWall).
- BPO Summit /exhibitors: title band + logo grid only. Improve: add search + sector filter and a profile page.
- GITEX exhibitor list: keyword search, country select, category select, A–Z letter chips, Clear filters; each row = logo, name, stand number, country, sector tags, View profile. Adopt: horizontal row cards with booth code in mono, sector tags, URL-synced filters, Clear filters link.
- GITEX speaker/partner interest pages: one strong H1 statement + a long single-column form split into titled groups. Adopt: grouped fieldsets with h3 group titles inside StepShell.
- Take Off /partners: photo-led dark hero with breadcrumb at top-left, 3-line H1 with one coloured line, two short paragraphs, one outline CTA; then benefits, then a contact form. Adopt: PageHero "photo" variant with breadcrumbs above the title and emphasis word in teal/gold.
- Take Off /press: H1 + list of article cards (image, title, date). Adopt: InfoCard article grid with category tabs.
- Take Off /faq: H1 band then a single accordion column. Adopt: Accordion in a max-w-3xl column, grouped by topic.
- All three: breadcrumbs, short heroes (not full-screen), one primary action per page, footer unchanged.
