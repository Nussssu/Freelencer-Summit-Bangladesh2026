"""Dev-time only: turn geoBoundaries BGD ADM0/ADM1 GeoJSON into the small projected JSON the scene ships.

Usage: python3 -I scripts/geo/build_geo.py <bgd-ADM0.geojson> <bgd-ADM1.geojson> resources/js/stage/geo.json
Source: geoBoundaries gbOpen (CC BY 4.0), https://www.geoboundaries.org
"""
import json, math, random, sys

LAT0, LNG0 = 23.7, 90.3
COS0 = math.cos(math.radians(LAT0))
NAMES = {'Barisal': 'Barishal', 'Chittagong': 'Chattogram', 'Rajshani': 'Rajshahi'}  # source spellings
CAPITALS = {'Dhaka': (23.8103, 90.4125), 'Chattogram': (22.3569, 91.7832), 'Rajshahi': (24.3745, 88.6042), 'Khulna': (22.8456, 89.5403),
            'Barishal': (22.7010, 90.3535), 'Sylhet': (24.8949, 91.8687), 'Rangpur': (25.7439, 89.2752), 'Mymensingh': (24.7471, 90.4203)}
ORDER = list(CAPITALS)


def proj(lng, lat):
    # Equirectangular around the country centre; 1 unit = 1 degree of latitude / 3 so the country fits roughly [-1, 1].
    return ((lng - LNG0) * COS0 / 3.0, (lat - LAT0) / 3.0)


def rings(geom):
    polys = geom['coordinates'] if geom['type'] == 'MultiPolygon' else [geom['coordinates']]
    return [p[0] for p in polys]


def area(r):
    return abs(sum(r[i][0] * r[i - 1][1] - r[i - 1][0] * r[i][1] for i in range(len(r)))) / 2


def dp(points, eps):
    if len(points) < 3:
        return points
    (x1, y1), (x2, y2) = points[0], points[-1]
    dx, dy = x2 - x1, y2 - y1
    norm = math.hypot(dx, dy) or 1e-12
    dmax, idx = 0, 0
    for i in range(1, len(points) - 1):
        d = abs(dy * points[i][0] - dx * points[i][1] + x2 * y1 - y2 * x1) / norm
        if d > dmax:
            dmax, idx = d, i
    if dmax <= eps:
        return [points[0], points[-1]]
    return dp(points[: idx + 1], eps)[:-1] + dp(points[idx:], eps)


def dp_closed(ring, eps):
    # A closed ring has a zero-length baseline; split at the vertex farthest from the start.
    far = max(range(len(ring)), key=lambda i: math.dist(ring[0], ring[i]))
    return dp(ring[: far + 1], eps)[:-1] + dp(ring[far:], eps)


def simplify(ring, target):
    lo, hi = 0.0, 0.5
    for _ in range(40):
        mid = (lo + hi) / 2
        if len(dp_closed(ring, mid)) > target:
            lo = mid
        else:
            hi = mid
    return dp_closed(ring, hi)


def inside(pt, ring):
    x, y = pt
    c = False
    for i in range(len(ring)):
        (x1, y1), (x2, y2) = ring[i - 1], ring[i]
        if (y1 > y) != (y2 > y) and x < (x2 - x1) * (y - y1) / (y2 - y1) + x1:
            c = not c
    return c


def r4(ring):
    return [[round(x, 4), round(y, 4)] for x, y in ring]


def main(adm0, adm1, out):
    rng = random.Random(2026)
    nat = sorted((r for f in json.load(open(adm0))['features'] for r in rings(f['geometry'])), key=area, reverse=True)
    main_ring = [proj(*p) for p in nat[0]]
    national = [simplify(main_ring, 250)] + [simplify([proj(*p) for p in r], 12) for r in nat[1:4]]

    divisions = {}
    for f in json.load(open(adm1))['features']:
        name = NAMES.get(f['properties']['shapeName'], f['properties']['shapeName'])
        big = max(rings(f['geometry']), key=area)
        divisions[name] = simplify([proj(*p) for p in big], 120)
    assert set(divisions) == set(ORDER), divisions.keys()

    caps = {n: proj(lng, lat) for n, (lat, lng) in CAPITALS.items()}
    ring0 = national[0]

    def sample_inside(poly, centre=None, sigma=0.0):
        while True:
            if centre:
                p = (centre[0] + rng.gauss(0, sigma), centre[1] + rng.gauss(0, sigma))
            else:
                xs, ys = zip(*poly)
                p = (rng.uniform(min(xs), max(xs)), rng.uniform(min(ys), max(ys)))
            if inside(p, poly) and inside(p, ring0):
                return p

    def division_of(p):
        for i, n in enumerate(ORDER):
            if inside(p, divisions[n]):
                return i
        return min(range(8), key=lambda i: math.dist(p, caps[ORDER[i]]))

    campuses = []
    for k in range(100):  # 60% clustered near division capitals, 40% spread across the country
        if k < 60:
            n = ORDER[k % 8]
            p = sample_inside(divisions[n], caps[n], 0.06)
        else:
            p = sample_inside(ring0)
        campuses.append([round(p[0], 4), round(p[1], 4), division_of(p)])

    districts = []  # up to 20 ring centres (brief mentions 12 and 20); the scene uses the first N from config
    while len(districts) < 20:
        p = sample_inside(ring0)
        if all(math.dist(p, d[:2]) > 0.11 for d in districts) and all(math.dist(p, c) > 0.05 for c in caps.values()):
            districts.append([round(p[0], 4), round(p[1], 4), division_of(p)])

    data = {
        'source': 'geoBoundaries gbOpen BGD ADM0/ADM1 (CC BY 4.0), simplified',
        'projection': {'lat0': LAT0, 'lng0': LNG0, 'scale': 1 / 3},
        'national': [r4(r) for r in national],
        'divisions': [{'name': n, 'ring': r4(divisions[n]), 'capital': [round(c, 4) for c in caps[n]]} for n in ORDER],
        'campuses': campuses,
        'districts': districts,
    }
    json.dump(data, open(out, 'w'), separators=(',', ':'))
    print('national pts', [len(r) for r in national], 'division pts', {n: len(divisions[n]) for n in ORDER})


if __name__ == '__main__':
    main(*sys.argv[1:4])
