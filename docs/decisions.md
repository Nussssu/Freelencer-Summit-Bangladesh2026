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
