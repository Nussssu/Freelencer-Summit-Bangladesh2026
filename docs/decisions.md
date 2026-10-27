# Decisions & brief discrepancies

Logged per instruction: where the client PDF and the build checklist differ, the PDF wins.

| # | Topic | PDF says | Handling |
|---|-------|----------|----------|
| 1 | Edition year | Cover/title "2027"; body "2026"; §2 "reusable for 2027 and beyond" | `SUMMIT_EDITION` config, default 2026 |
| 2 | District count | §6/§15 "12 districts"; §4 table "20 districts"; Appendix A "20 District Summits" | `SUMMIT_DISTRICTS` config, default 12 |
| 3 | Date / venue | "Late November 2026, Dhaka (exact date/venue to be confirmed)" | `SUMMIT_STARTS_AT` placeholder, `SUMMIT_VENUE` null = "To be confirmed" |
| 4 | Tagline / logo | Listed under "Content inputs required" (§21) | Placeholder tagline in config; text wordmark placeholder |
| 5 | Nav label vs URL | Nav "District Summits"; URL `/district-roadshows`; §4 "Freelancer roadshow" | Nav label kept verbatim, links to `/district-roadshows` |
| 6 | Divisional Summits | Only appear in §6 & §15 (not in §4 phase table) | Kept, `/divisional-summits` route per §19 |
| 7 | Award statuses | §9 lists 5 incl. "Rejected/Incomplete" | Homepage stitch shows 4 public-facing steps only (Rejected is admin-only) |
| 8 | Featured speakers | §13 asks for a "featured speaker slider" | Section 7 includes a featured showcase + card rail (swipe on mobile) |
| 9 | Session types | §14 list of 11 types | Used verbatim as agenda filter tags |
| 10 | Sponsor tiers | "final naming to be confirmed" | Tier names in PHP content, marked provisional |
| 11 | Inertia version | Spec: Inertia v2 | Latest is v3; pinned inertia-laravel ^2.0 and @inertiajs/vue3 ^2.3 as specified (v2 supports Laravel 13) |
| 12 | Route count | Spec says "27 routes"; the stub list has 27 URLs plus "/" | 28 routes tested ("/" + 27) |
| 13 | claude-design skill vs brief | Skill: soft shadows, rounded radius scale | Brief shape language wins: 0–2px radius, no shadows; skill's 4px grid, tokens, focus-visible, 44px targets kept |
| 14 | Header width | Wordmark + lockup + 5 links + countdown + 4 controls don't fit one 1440px row | BPO's measured two-tier header (B3): main bar = wordmark, lockup, Register, Nominate, Get Involved, MENU; slim nav row (≥1280) = 5 inline links; mini countdown in utility bar (1024–1535) or main bar (≥1536) |
| 15 | Hero message interval | Take Off measured 7000ms | Brief says ~5s → 5000ms, Take Off's 800ms slide kept |
| 16 | Magnetic hover | Measured hovers have no transform | useMagnetic only on the hero Register button (max 6px) |
| 17 | SSR + Vue 3.5.44 | Inertia v2.3 SSR rendered "[object Promise]" | resources/js/ssr.ts wraps renderToString with a placeholder swap (comment explains); remove when Inertia fixes it |
| 18 | Video format | No ffmpeg on the build machine | Fallback clips are VP9 WebM only (0.94MB / 0.82MB); browsers without WebM keep the poster |
