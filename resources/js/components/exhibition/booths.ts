// Booth record from ExhibitorQuery::booths() and the status colours shared by the map, legend and dialog.
export type Booth = { code: string; status: string; size: string | null; x: number; y: number; w: number; h: number; sample: boolean };

export const BOOTH_FILL: Record<string, string> = { available: '#ffffff', reserved: '#f6d98a', confirmed: '#f7a3ad' };

/*
 * Illustrative hall plan (venue-plan style): walls, entrance, pavilions, amenity blocks and stand islands, in a
 * 1800 × 940 drawing. Stand codes are matched against the CMS booths for their status; a stand with no booth record
 * shows as available. Replace this file's geometry with the venue's plan once it is final.
 */
export const PLAN_W = 1800;
export const PLAN_H = 940;

export type Rect = { x: number; y: number; w: number; h: number };
export type Stand = Rect & { code: string; sqm: number };
export type Zone = Rect & { lines: string[]; tag: string; from: string; to: string; gold?: boolean };
export type Amenity = Rect & { label: string; vertical?: boolean };

// Outer wall with the entrance gap on the right (y 470–600)
export const WALL = 'M1560 470V200H1500V140H1350V100H1150V140H640V110H420V140H60V880H1560V600';
export const FLOOR = 'M60 140H420V110H640V140H1150V100H1350V140H1500V200H1560V880H60Z';
export const ENTRANCE = { x: 1560, y: 470, h: 130 };

export const ZONES: Zone[] = [
    { x: 90, y: 160, w: 170, h: 110, lines: ['Startup Zone'], tag: 'Free', from: '#8a9bb3', to: '#2f8fdc' },
    { x: 90, y: 300, w: 170, h: 210, lines: ['Marketplaces', '& Platforms'], tag: 'Gold', from: '#9c1457', to: '#e8306b', gold: true },
    { x: 90, y: 540, w: 170, h: 210, lines: ['BPO & ITES'], tag: 'Gold', from: '#9c1457', to: '#e8306b', gold: true },
    { x: 660, y: 460, w: 180, h: 110, lines: ['Training', 'Institutes'], tag: 'Free', from: '#10265c', to: '#1f4fa8' },
    { x: 660, y: 570, w: 180, h: 110, lines: ['Universities'], tag: 'Free', from: '#b0175f', to: '#e8306b' },
    { x: 980, y: 460, w: 160, h: 115, lines: ['Fintech &', 'Payments'], tag: 'Free', from: '#21c6c9', to: '#1fa0e0' },
    { x: 980, y: 575, w: 160, h: 115, lines: ['Career', 'Fair'], tag: 'Gold', from: '#8a3a10', to: '#f06a1c', gold: true },
    { x: 1430, y: 250, w: 100, h: 210, lines: ['Freelance', 'Services'], tag: 'Gold', from: '#0a6fa0', to: '#26c6e8', gold: true },
];

export const AMENITIES: Amenity[] = [
    { x: 455, y: 195, w: 105, h: 45, label: 'Chill & Charge' },
    { x: 565, y: 150, w: 95, h: 90, label: 'Catering' },
    { x: 660, y: 375, w: 150, h: 65, label: 'Lounge' },
    { x: 300, y: 790, w: 185, h: 75, label: 'BACCO Connect' },
    { x: 510, y: 790, w: 185, h: 75, label: 'Learning Hub' },
    { x: 1435, y: 790, w: 95, h: 75, label: 'Catering' },
    { x: 1375, y: 285, w: 45, h: 60, label: 'Info Desk' },
    { x: 1475, y: 485, w: 24, h: 165, label: 'Registration', vertical: true },
];

const hits = (a: Rect, b: Rect, m = 6) => a.x < b.x + b.w + m && b.x < a.x + a.w + m && a.y < b.y + b.h + m && b.y < a.y + a.h + m;

// Stand islands on an aisle grid, skipping anything a pavilion or amenity covers. Islands vary between 4 × 9 m²,
// 2 × 18 m² and one 36 m² stand so the plan reads like a real hall.
function islands(): Stand[] {
    const out: Stand[] = [];
    const blocked: Rect[] = [...ZONES, ...AMENITIES];
    let n = 100;
    let k = 0;
    const push = (x: number, y: number, w: number, h: number, sqm: number) => out.push({ code: String((n += 5)), x, y, w, h, sqm });
    for (const y of [290, 375, 460, 545, 630, 715, 795]) {
        for (let x = 290; x + 80 <= 1400; x += 92) {
            const isl = { x, y, w: 80, h: 70 };
            if (blocked.some((b) => hits(isl, b))) continue;
            const v = k++;
            if (v % 6 === 2) push(x, y, 80, 70, 36);
            else if (v % 6 === 4) [0, 35].forEach((dy) => push(x, y + dy, 80, 35, 18));
            else if (v % 9 === 7) [0, 40].forEach((dx) => push(x + dx, y, 40, 70, 18));
            else [0, 35].forEach((dy) => [0, 40].forEach((dx) => push(x + dx, y + dy, 40, 35, 9)));
        }
    }
    // Large stands along the top wall
    push(780, 155, 80, 65, 36);
    push(985, 155, 80, 65, 36);
    push(1190, 150, 40, 45, 18);
    return out;
}
export const STANDS: Stand[] = islands();
