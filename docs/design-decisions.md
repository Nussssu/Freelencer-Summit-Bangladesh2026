# Design decisions: "The Delta Signal"

One line per token: value, then source (`measured:<site> <finding-id>` from reference-audit.md, or `default`).

## Type
- font-display: Big Shoulders Variable (wght 100–900, opsz), @fontsource-variable/big-shoulders, measured:gitex G1
- font-body/ui: Host Grotesk Variable, @fontsource-variable/host-grotesk, measured:gitex G1 (UI face; replaces GITEX body Poppins, which is banned as a main face)
- font-mono (labels, status tags, chips): JetBrains Mono Variable, default (no mono measured)
- font-bangla: Anek Bangla Variable (wght, wdth), Hind Siliguri fallback, default (brief rule)
- substitutions logged: D-DIN (BPO, commercial) → Big Shoulders; Inter (Take Off, banned) → Big Shoulders for display; Poppins (GITEX body, banned) → Host Grotesk
- width-axis animation: skipped; Big Shoulders has no wdth axis (Anek Bangla's wdth is used only on the Bangla title in the preloader), default
- h1: Big Shoulders 900, UPPERCASE, tracking 0, line-height 0.86, clamp(4.5rem, 12.5vw, 12rem); one word per headline is emphasised (outlined or colour), measured:takeoff T1 (900, uppercase, normal tracking, emphasis inside one line), scale measured:gitex G1 (vw scaling)
- h2: Big Shoulders 900, UPPERCASE, line-height 0.92, clamp(2.75rem, 6vw, 5.4rem), measured:gitex G1 (6vw = 86.4px @1440)
- h2-xl (statement and final CTA): clamp(3.5rem, 8.6vw, 7.75rem), measured:gitex G1 (8.6vw = 123.84px); final CTA line 15vw, brief
- h3: Host Grotesk 600 1.25rem/1.2, measured:gitex G1 (20.16px)
- body: Host Grotesk 400 1rem/1.55 (16px; GITEX 14.4px raised for legibility and AA), measured:gitex G1 + default
- label: JetBrains Mono 500 0.75rem, UPPERCASE, tracking 0.08em, default
- button: Host Grotesk 700 0.875rem UPPERCASE, measured:gitex G1 (700 14.4px) + takeoff T1 (uppercase buttons)
- nav-inline: Host Grotesk 500 0.875rem UPPERCASE, measured:bpo B1 (uppercase nav)
- stat: Host Grotesk 800 clamp(2.5rem, 3.5vw, 3.1rem), tabular-nums, measured:gitex G7 (37.4–49.5px, 800)

## Colour (never #000 / #fff)
- night #050E0A (dark surfaces, text on light), default
- delta #0A3B2C (deep green surface), default
- flag-green #006A4E, default (Bangladesh flag; matches the widely cited spec value, official spec gives Pantone/ratios only)
- flag-red #F42A41, default (same note)
- jute-gold #E9B949 (primary CTA, the "accent"), default; role mirrors GITEX yellow CTA pill, measured:gitex G2/G10
- river-mint #7FE0C8 (particles, focus ring on dark), default
- paper #F2ECDF (light surfaces), default
- muted #8FA096 (secondary text on dark only), default
- ink-2 #3F4B44 (secondary text on paper; 7.8:1), default
- bacco-blue #3167AE (organiser "family" colour: lockup, forum section accents), measured:bpo B2 (dominant brand accent)
- line-dark rgba(242,236,223,.14) / line-light rgba(5,14,10,.14) hairlines, measured:gitex G2 (.36 on red, lowered for dark)
- ratio target ≈ 55% dark (night/delta) / 25% paper / 12% green / 6% red / 2% gold. GITEX measured 55% light / 20% brand / 15% dark / 5% accent (G2); its ~25% brand-to-neutral share is kept, but light/dark is inverted because the canvas-driven hero and pinned journey need a dark ground. Logged deviation.
- section rhythm: hero dark → strip paper → what-is paper → journey dark → why dark(delta) → highlights RED (the loud break) → speakers night → agenda paper → awards green+gold → expo/sponsor split paper|delta → forum paper (bacco-blue accents) → stories night → partners paper → news paper → gallery night → final CTA flag-green → footer night, measured:gitex G6 (alternating bands) + brief
- contrast (computed): paper/night 16.6; night/gold 10.7; paper/delta 10.7; paper/green 5.6; night/red 4.9 (red buttons use night text); paper/blue 4.9; muted/night 7.1; muted/delta 4.6; ink-2/paper 7.8; paper/red 3.4 and gold/green 3.6 → display sizes (≥24px) only; muted never on paper (2.3)

## Space and layout
- container 1320px max + 16px gutter (mobile) / 32px (≥768), measured:gitex G6
- section-y: clamp(4rem, 7vw, 7.5rem); GITEX measured 43–57px (3–4vw) + 228px major breaks; raised because our headings are denser, measured:gitex G6 (adjusted)
- header: utility bar 36px + main bar 76px = 112px (≈ GITEX scrolled bar 115px), measured:gitex G3; date/venue chips + organiser lockup + register button, measured:bpo B3
- header after hero: solid night with a hairline, measured:gitex G3 (state swap on scroll)
- mobile header 64px + sticky bottom bar 64px, measured:bpo B11 (73px) adjusted
- grid: 12 columns, gap 24px; faint column lines (line-dark at 5%) on dark sections, default (brief)

## Shape
- radius 0; 2px only on inputs, default (brief); deliberate deviation from GITEX 40px pills (G10) and Take Off 26.5px (T1)
- buttons: sharp rectangle, top-right corner notched 10px via clip-path, default (brief)
- borders 1px hairline at 14% opacity, measured:gitex G2 (hairlines) + default
- shadows: none, measured:bpo B9 / gitex G10 (no shadow on hover)
- film grain 3% on dark sections only (SVG noise, `mix-blend-mode: overlay`), default (brief)
- kantha stitch: `stroke-dasharray: 6 5`, 1.5px, round caps, slight y-jitter path, default (concept)

## Motion
- hover: 0.3s ease-in-out, colour swap only, no transform, measured:gitex G10/G11 (+ bpo B9, takeoff T9 all 0.3s)
- primary button hover: gold → flag-red, text stays night, measured:gitex G10 (yellow → orange swap)
- nav hover: colour → gold, measured:bpo B9 / takeoff T9 (colour-only)
- reveal ease: expo-out `cubic-bezier(0.16, 1, 0.3, 1)` / GSAP `expo.out`, 0.9s; most frequent measured curve is CSS `ease`, used by all three for UI transitions, not reveals; no reveal timeline measured, default
- stagger 55ms, default
- each reveal plays once, default
- count-up: rolling digit ribbons, start "0+", trigger on enter, 2.2s expo-out, measured:gitex G7 (odometer pattern; duration default because GITEX ~3.7s)
- hero message slider: 4 slides, 5000ms (brief; Take Off measured 7000ms), 800ms slide transition, prev/next arrows, measured:takeoff T5; segmented progress ticks, brief
- marquee: 40s linear loop per strip, measured:takeoff T8 (continuous strip), duration default
- menu overlay: clip-path circle/inset reveal 0.7s expo-out; GITEX uses 0.3s opacity dropdown (G9), full-screen per brief, Take Off full-screen 0.5s (T9)
- drawer: translateX 100% → 0, 0.6s expo-out, default (BPO uses a modal, B6)
- pin/scrub: journey pinned ~450vh, scrub 1 (smoothed), default (no pin measured on any site)
- Lenis: lerp 0.085, wheelMultiplier 1, default (no smooth scroll measured)
- route wipe: night panel scaleY 0→1→0, 0.45s each way, default
- preloader ≤ 1.6s, once per session, default (brief)
- reduced motion: no pin, no Lenis, no cursor, static poster, counters show final values, default

## Components (pattern source)
- countdown under CTAs, BPO placement + GITEX numeric scale, measured:bpo B4 + gitex G7
- speaker role label + Details → right drawer, measured:bpo B6 (reinterpreted)
- exhibit enquiry popup fields, measured:bpo B7
- ticker strip, measured:gitex G5
- tier bands with hairline-flanked labels, measured:gitex G8
- footer giant cropped wordmark, measured:gitex G13
- numbered rows 01–09, measured:takeoff T6
- accent "+" on stats, measured:takeoff T7
- quote carousel with tags, measured:takeoff T8
