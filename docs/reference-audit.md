# Reference audit (measured 2026-10-27, Playwright, 1440x900 + 390x844)

Method: computed styles via script evaluation after a 700px-step slow scroll; scroll probe at 0/25/50/75% (nudge 120px, diff transform/opacity/clip-path/top); hover = computed before/after 500ms. Finding IDs (B1, G3, T2…) are referenced from `design-decisions.md`.

## A. BPO Summit Bangladesh — bposummit.org.bd (same organiser: BACCO)
- **B1 Fonts**: H1 "D-DIN" 900 40/60 capitalize; H2 D-DIN 800 40/60 with blue→orange gradient text; H3 Plus Jakarta Sans 600 20/34; body PJS 600 14/20; nav D-DIN 500 16 UPPERCASE; button Roboto 500 15. Loaded: D-DIN 600 (commercial), elementskit icons.
- **B2 Palette (area)**: white 6.7M px², #F3F3F3 / #F8F9FA 2.7M, navy #153663 (footer) 0.35M, orange #F38121 0.10M (countdown tiles), peach #FFDDBD (role chips). Text #0C141D. **Dominant brand accent: BACCO blue #3167AE** (H2, buttons, active nav), secondary orange #F38121.
- **B3 Header**: 166px = utility row 82px (logo | calendar icon "20 June, 2026" + pin icon "Khulshi Convention Hall, Chattogram" | "Jointly Organized By" + BACCO + BPC logo boxes) + nav row 84px (8 UPPERCASE links + "Registration" button). Nav row becomes sticky. Mobile header 73px: logo, "Free Registration" text link, toggle.
- **B4 Layout**: container `min(100%, 1300px)`. Hero ≈415px: event photo + dark overlay (rgba(0,0,0,.8) layer), centred H1, date+venue line, one CTA. **Countdown sits under the CTA**: 4 orange tiles (digits 45px/700, label 16px) in a white rounded card overlapping the hero's bottom edge.
- **B5 Schedule**: 2-col cards, 184px photo left + title (20px/600) + date and time with icons.
- **B6 Speakers**: 3-col white cards, photo, **role chip** (Chief Guest of Inauguration / Special Guest / Guest / Speaker, peach bg, 14px), name 18px/700, designation, organisation bold, orange "Details →". Details opens a **centred modal** (713px wide) with round photo + long bio. Escape does **not** close it.
- **B7 Enquire to Exhibit** (footer link): 2-step modal with a stepper (1 Company Info → 2 Personal info). Step 1: Company Name*, Website URL, Industry / Sector, Head Office Location; step 2: personal contact (Full Name, Designation, Email, Phone/WhatsApp per brief).
- **B8 Motion**: eases `ease` 110, `ease-out` 75, `ease-in` 72; durations 0.3s (50), 0.4s (29), 0.15s (17). Keyframes are Elementor entrance (`fadeInUp`, `fadeIn`). No scroll-linked transforms found at any probe position; only the sticky nav moves.
- **B9 Hover**: Registration bg #3167AE → #0C141D, text white, radius 3px, pad 12×24, 0.3s, no transform or shadow. Nav link #0C141D → #3167AE.
- **B10 Libraries**: jQuery 3.7.1, Elementor, Swiper. No GSAP, Lenis, three.js, canvas or video.
- **B11 Mobile**: H1 28px; menu is a dropdown list (centred items, dark active row) under a 73px bar.
- **B12 Footer**: navy, logo, Terms | Privacy | Enquire To Exhibit, contact (venue/email/phones) right, square social icons.

## B. GITEX Global — gitex.com
- **G1 Fonts**: loaded **Big Shoulders** 700/900 (OFL), **Host Grotesk** 400/700/800 (OFL), Poppins 300/400/600. Section H2 Big Shoulders 900 UPPERCASE, line-height 0.95, sizes scale with viewport: 123.84px (8.6vw), 86.4px (6vw), 63.36px (4.4vw). H3 Host Grotesk 600 20.16. Body Poppins 400 14.4/21.6. Buttons Host Grotesk 700 14.4. Nav (menu) Host Grotesk 400 20/30.
- **G2 Palette (area)**: white 0.96M, light grey #E0E1DB 0.72M, black 0.41M (hero video), **red #D70000** 0.40M + #EC0A10, red→orange #F3933C gradients (header bar, stats band, footer), **CTA yellow #FCFF34**. Hairline borders rgba(255,255,255,.36) on colour. Ratio ≈ 55% light neutral / 20% brand red-orange / 15% dark media / 5% accent yellow.
- **G3 Header**: fixed, transparent over the hero: logo lockup with stacked date chips ("EXPO 8–11 DEC · SUMMIT 7 DEC · DUBAI WORLD TRADE CENTRE"), right: GET TICKETS pill + secondary yellow pill + "MENU ▾". After the hero it becomes a 115px red→orange gradient bar with rounded bottom corners and the CTA swaps to EXHIBIT NOW. Fixed vertical social rail on the right edge.
- **G4 Hero**: 1428×792 (88vh) `<video autoplay muted loop>` 11.2s mp4, object-fit cover, preload metadata, **no poster**. H1 Big Shoulders ~95px white, left-aligned in the lower half; two pill CTAs bottom-right.
- **G5 News ticker**: a dark translucent strip pinned to the hero's bottom edge: bold "Latest News" label + headlines separated by yellow bars, continuous CSS marquee.
- **G6 Layout**: container 1320px. Section padding 43–57px top/bottom (3–4vw) with one 228px major break; headings carry the scale. Rhythm: dark video → white sponsors → grey #E0E1DB → red/orange stats band → white → red footer.
- **G7 Counters**: odometer.js. Numbers render as rolling digit ribbons ("0123456789…"), stay at 0 until in view, then roll and settle on "1,500+ / 30+ / 100+ / 150+" in under ~4s (samples at 0/600/1200ms were still rolling, settled by 3.7s). Host Grotesk 800, 37.4px (summit block) / 49.5px (expo block), label 14px under each.
- **G8 Sponsor tiers**: centred tier labels flanked by hairlines ("PLATINUM SPONSOR", "GOLD SPONSOR", "BRONZE SPONSOR", "CONFERENCE HEADLINE SPONSOR", …), logos in full colour, tier size shrinks down the wall, tiers paired two-up.
- **G9 Menu**: "MENU" opens a 360×266 dropdown (rgba(10,10,10,.9)) under the header, opacity 0.3s ease-in-out. Top items: Plan your visit, Hotels, Exhibitor list; grouped **GET INVOLVED / LEARN / GITEX UNIVERSE** expand as accordions with chevrons. Escape does **not** close it. Not full-screen.
- **G10 Hover**: GET TICKETS bg #FCFF34 → #F3933C, text black → white, radius 40px, 0.3s ease-in-out, no transform or shadow.
- **G11 Motion**: eases `ease` 135, `ease-in-out` 72; durations 0.3s (40), 0.15s (32), 0.4s (26), 0.35s (19). Keyframes are UIkit (`uk-fade`, `uk-slide-*`, `uk-kenburns`). No GSAP/ScrollTrigger; scroll probe found no scrubbed or parallax transforms, only the fixed header/rail.
- **G12 Libraries**: jQuery 3.7.1, UIkit, Bootstrap variables, odometer.js. No canvas, no three.js. One autoplay video.
- **G13 Footer**: red→orange gradient, a giant translucent "GITEX" wordmark (~750px wide) behind the columns; a giant grey "GITEX GLOBAL" watermark cropped above the footer. Columns: logo + GET TICKETS + #GITEXGLOBAL | Get Involved | Learn (accordion) | Let's Talk (separate inquiry emails per journey: Sales, Marketing, Conferences, Partnerships) | legal row.
- **G14 Mobile**: hero H1 **overflows the viewport** ("2027" clipped at 390px); header CTAs wrap under the logo. Speaker grid / featured-speaker showcase: **not present on the homepage at audit time** (recorded as "default").

## C. Take Off Istanbul — takeoffistanbul.com/en
- **T1 Fonts**: Inter variable 100–900 only. **H1 900 96/96 UPPERCASE, tracking normal**, 2 lines ("WHERE GLOBAL / DREAMS TAKE OFF"), revealed by a masked text animation over the video. Section H4 800 48–56px UPPERCASE with **one or two words in an accent colour inside the same headline** (red #C0392B / blue #2D50F8). Buttons 700 16 UPPERCASE, 1px border, radius 26.5px. Nav 500 16.
- **T2 Palette (area)**: #080808 5.6M, #000 1.3M, #070302 1.3M, #1A1A1A 0.19M, **blue #2D50F8** 0.09M, #FAFAFA 0.08M. Text #FAFAFA, 80% and 60% alpha variants. Accents red #C0392B / #DE2B30. ≈ 92% dark / 3% light / 5% accent.
- **T3 Header**: 74px fixed, #080808, logo left, ABOUT / TAKE OFF dropdowns, "TR" language toggle as a 56px circle-outline button. Container 1280px, section padding 80/80 (60 and 150 variants).
- **T4 Hero**: 900px, autoplay muted loop webm (95s) behind the H1. Edition badge **"'26 / TAKE OFF ISTANBUL"** sits on a gradient card in the next section.
- **T5 Rotating messages**: Swiper, **4 slides, autoplay 7000ms, speed 800ms, effect "slide"** (horizontal), each slide = H4 headline with coloured emphasis + one paragraph; **two 35px circular outline arrows** (prev/next) under the text. No progress ticks.
- **T6 Numbered cards**: "01" / "02" in 40px/800 blue #2D50F8 as large index numerals.
- **T7 In Numbers**: 4×2 grid, numbers ~56px/900 white with the "+" or "$" in blue, label UPPERCASE 24px/700 directly under. Static (no count-up observed).
- **T8 Carousels**: quote carousel 8 slides, autoplay 3000ms, speed 2000ms; video list 5 slides, 10000ms; photo strip 11 slides, `slidesPerView: auto`, speed 20000ms continuous marquee.
- **T9 Hover / motion**: nav colour #FAFAFA → #DE2B30, `0.3s cubic-bezier(0.4, 0, 0.2, 1)` (Tailwind default). Stylesheet eases: `ease` 24; durations 0.1s. Mobile menu: full-screen black overlay, 0.5s cubic-bezier(0.4,0,0.2,1).
- **T10 Libraries**: Tailwind, jQuery 3.7.1, Swiper, SweetAlert2. No GSAP, Lenis, canvas. Scroll probe: no scroll-linked transforms (only the fixed header).
- **T11 FAQ**: "FREQUENTLY ASKED QUESTIONS" H2 56/700; accordion structure was not reachable by the probe, so open/close timing is recorded as "default".
- **T12 Mobile**: H1 60px, menu = full-screen black overlay listing all items + TR toggle.

## Cross-site summary
- None of the three sites uses GSAP, ScrollTrigger, Lenis or WebGL; all motion is CSS transitions (0.3s dominant) + entrance keyframes + Swiper. Pinning, scrub, smooth-scroll values: **not measured → defaults**.
- Most frequent easing everywhere is CSS `ease`; hover duration 0.3s on all three.
- Esc does not close BPO's modal or GITEX's menu (accessibility gap to fix in ours).

## What it does well / What we adopt (reinterpreted) / What we deliberately avoid

| What it does well | What we adopt (reinterpreted) | What we deliberately avoid |
|---|---|---|
| BPO: date + venue + organiser lockup visible in the header (B3) | Utility bar chips "LATE NOV {edition}" / "DHAKA, BANGLADESH" + "BACCO × DoICT" lockup | Two stacked white bars totalling 166px; logo boxes with soft shadows |
| BPO: countdown directly under the CTA (B4) | Countdown under the hero CTAs, GITEX numeric scale, rolling digits | Orange rounded tiles in a floating white card |
| BPO: role chips on speakers (B6) | Mono role label (Chief Guest / Special Guest / Guest / Speaker) | Peach pill chips, 3 identical white cards |
| BPO: Details bio (B6) | Bio in a right-side off-canvas drawer, focus trap, **Esc closes** | Centred modal that ignores Escape |
| BPO: 2-step exhibit enquiry (B7) | Same 8 fields, posted to Laravel, inline server errors | Unlabelled placeholder-only inputs |
| GITEX: condensed uppercase display scaled in vw (G1) | Big Shoulders 900 uppercase as display face, vw-scaled clamp | Overflowing H1 on mobile (G14) |
| GITEX: hero video loop (G4) | 3D canvas on desktop; 6s pre-rendered loop **with poster** as fallback | Video without poster |
| GITEX: news ticker at hero/strip edge (G5) | One-line announcement ticker inside section 2 | Real headlines we don't have — placeholders only |
| GITEX: odometer counters from 0+ (G7) | Rolling-digit count-up for the 4 journey counters | Invented numbers; values come from config (start 0) |
| GITEX: named sponsor tiers between hairlines (G8) | Tier band headers with hairlines, dashed empty slots | Full-colour logos at rest (ours greyscale → colour on hover) |
| GITEX: grouped menu, always-visible CTAs (G3, G9) | Full-screen MENU with grouped panels + 5 action tiles | 360px dropdown, Esc not handled |
| GITEX: giant footer wordmark (G13) | Cropped giant wordmark with slow parallax | Red→orange gradient wash |
| Take Off: uppercase 900 headline, colour emphasis inside one line (T1) | H1/H2 uppercase 900 with one emphasised word (outline or colour) | Inter as the main typeface |
| Take Off: 4 rotating messages with arrows (T5) | 4 hero messages, 800ms slide, arrows + segmented progress ticks | 7s delay (spec asks ~5s) |
| Take Off: 01/02 index numerals (T6) | Numbered audience rows and benefit rows | Blue #2D50F8 accent |
| Take Off: In Numbers grid with accent "+" (T7) | Accent-coloured "+" on journey counters | Static numbers |
| Take Off: quote carousel, video list (T8) | Quote carousel with programme tags; click-to-load video tiles | 2s cross-slide speed |
