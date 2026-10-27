import * as THREE from 'three';
import geo from './geo.json';
import { stage } from './state';

/**
 * "Delta Flow": particles run along river channels (source → division capital → Dhaka), then the same
 * scene morphs into a tilted map for the National Journey: 100 campuses → district rings → 8 division
 * nodes → kantha-stitched arcs into Dhaka. Plain three.js, additive points, no postprocessing.
 */

type V2 = [number, number];
const smooth = (a: number, b: number, x: number) => {
    const t = Math.min(1, Math.max(0, (x - a) / (b - a)));
    return t * t * (3 - 2 * t);
};
const rand = (seed: number) => () => ((seed = (seed * 16807) % 2147483647) - 1) / 2147483646;

function inside([x, y]: V2, ring: number[][]) {
    let c = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
        const [x1, y1] = ring[j], [x2, y2] = ring[i];
        if (y1 > y !== y2 > y && x < ((x2 - x1) * (y - y1)) / (y2 - y1) + x1) c = !c;
    }
    return c;
}

const FLOW_VERT = /* glsl */ `
attribute vec2 aSrc; attribute vec2 aCap; attribute vec4 aSeed;
uniform float uTime, uSize, uPixelRatio, uFlow, uSpeed, uMouseOn;
uniform vec2 uDhaka, uMouse;
varying float vAlpha, vT, vMix;
vec2 bez(vec2 a, vec2 b, vec2 c, float t) { float u = 1. - t; return u*u*a + 2.*u*t*b + t*t*c; }
void main() {
  float t = fract(aSeed.x + uTime * uSpeed * (0.55 + 0.45 * aSeed.y));
  vec2 p = bez(aSrc, aCap, uDhaka, t);
  vec2 tg = bez(aSrc, aCap, uDhaka, min(t + 0.01, 1.)) - p;
  vec2 n = normalize(vec2(-tg.y, tg.x) + 1e-6);
  p += n * ((aSeed.z - .5) * .05 * (1. - t * .85) + sin(t * 18. + aSeed.w * 6.283 + uTime * .8) * .006);
  vec2 d = p - uMouse;
  p += normalize(d + 1e-6) * .07 * smoothstep(.2, 0., length(d)) * uMouseOn;
  vec4 mv = modelViewMatrix * vec4(p, sin(p.x * 6. + uTime * .5) * .012, 1.);
  gl_Position = projectionMatrix * mv;
  gl_PointSize = uSize * uPixelRatio * (.6 + aSeed.y) / -mv.z;
  vAlpha = smoothstep(0., .08, t) * (1. - smoothstep(.78, 1., t) * .85) * mix(.12, 1., uFlow);
  vT = t; vMix = aSeed.w;
}`;
const FLOW_FRAG = /* glsl */ `
uniform vec3 uMint, uGreen, uGold; uniform float uOpacity;
varying float vAlpha, vT, vMix;
void main() {
  float r = length(gl_PointCoord - .5);
  if (r > .5) discard;
  vec3 col = mix(mix(uGreen, uMint, vMix), uGold, smoothstep(.75, 1., vT));
  gl_FragColor = vec4(col, smoothstep(.5, 0., r) * vAlpha * uOpacity);
}`;

const NODE_VERT = /* glsl */ `
attribute vec2 aCampus; attribute vec2 aRing; attribute vec2 aNode; attribute float aSeed;
uniform float uTime, uS1, uS2, uSize, uPixelRatio;
varying float vTw;
void main() {
  vec2 p = mix(mix(aCampus, aRing, uS1), aNode, uS2);
  vec4 mv = modelViewMatrix * vec4(p, .004, 1.);
  gl_Position = projectionMatrix * mv;
  vTw = .55 + .45 * sin(uTime * 2.2 + aSeed * 40.);
  gl_PointSize = uSize * uPixelRatio * (1. + uS2 * .6) / -mv.z;
}`;
const NODE_FRAG = /* glsl */ `
uniform vec3 uMint, uGold; uniform float uOpacity, uS2;
varying float vTw;
void main() {
  float r = length(gl_PointCoord - .5);
  if (r > .5) discard;
  float a = smoothstep(.5, .15, r) + smoothstep(.5, 0., r) * .4;
  gl_FragColor = vec4(mix(uMint, uGold, uS2), a * mix(vTw, 1., uS2) * uOpacity);
}`;

const BEACON_FRAG = /* glsl */ `
uniform float uTime, uSwell; uniform vec3 uRed, uPaper;
varying vec2 vUv;
void main() {
  float r = length(vUv - .5) * 2.;
  float core = smoothstep(.13, .07, r);
  float ph = fract(uTime / 2.4);
  float ring = smoothstep(.035, 0., abs(r - ph)) * (1. - ph);
  float glow = smoothstep(.5, 0., r) * .25 * (.6 + uSwell);
  vec3 col = mix(uRed, uPaper, core * .35);
  gl_FragColor = vec4(col, core + ring * .9 + glow);
}`;

export interface SceneOptions {
    canvas: HTMLCanvasElement;
    labels: HTMLElement[]; // 8 division labels (geo order) + beacon label last
    districts: number;
    particles?: number;
}

export class DeltaScene {
    private renderer: THREE.WebGLRenderer;
    private scene = new THREE.Scene();
    private camera = new THREE.PerspectiveCamera(35, 1, 0.1, 50);
    private map = new THREE.Group();
    private flow: THREE.Points<THREE.BufferGeometry, THREE.ShaderMaterial>;
    private nodes: THREE.Points<THREE.BufferGeometry, THREE.ShaderMaterial>;
    private beacon: THREE.Mesh<THREE.PlaneGeometry, THREE.ShaderMaterial>;
    private outline: THREE.LineLoop<THREE.BufferGeometry, THREE.LineBasicMaterial>;
    private divisionLines: THREE.LineLoop<THREE.BufferGeometry, THREE.LineBasicMaterial>[] = [];
    private arcs: THREE.Line<THREE.BufferGeometry, THREE.LineDashedMaterial>[] = [];
    private nodePositions: THREE.Vector3[];
    private raycaster = new THREE.Raycaster();
    private plane = new THREE.Plane();
    private hit = new THREE.Vector3();
    private tilt = { x: 0, y: 0 };
    private last = 0;
    private elapsed = 0;
    private raf = 0;
    private frames = 0;
    private slowFor = 0;
    private fpsWindow = 0;
    private degraded = false;
    private width = 1;
    private height = 1;
    fps = 60;

    constructor(private opts: SceneOptions) {
        this.renderer = new THREE.WebGLRenderer({ canvas: opts.canvas, antialias: false, alpha: true, powerPreference: 'high-performance' });
        this.renderer.setPixelRatio(Math.min(devicePixelRatio, 1.5));
        this.renderer.setClearColor(0x000000, 0);
        this.camera.position.set(0, 0, 2.8);

        const css = getComputedStyle(document.documentElement);
        const color = (name: string, fallback: string) => new THREE.Color(css.getPropertyValue(name).trim() || fallback);
        const C = { mint: color('--mint', '#7FE0C8'), green: color('--green', '#006A4E'), gold: color('--gold', '#E9B949'), red: color('--red', '#F42A41'), paper: color('--paper', '#F2ECDF') };

        const dhaka = geo.divisions[0].capital as V2;
        const caps = geo.divisions.map((d) => d.capital as V2);
        const national = geo.national[0];
        const r = rand(2026);

        // --- River flow particles
        const N = opts.particles ?? 40000;
        const src = new Float32Array(N * 2), cap = new Float32Array(N * 2), seed = new Float32Array(N * 4);
        const loose: [number, number, number][] = [];
        while (loose.length < 600) {
            const p: V2 = [r() * 1.5 - 0.72, r() * 2 - 1];
            if (inside(p, national)) loose.push([p[0], p[1], caps.reduce((best, c, i) => (Math.hypot(c[0] - p[0], c[1] - p[1]) < Math.hypot(caps[best][0] - p[0], caps[best][1] - p[1]) ? i : best), 0)]);
        }
        for (let i = 0; i < N; i++) {
            const s = r() < 0.7 ? geo.campuses[i % geo.campuses.length] : loose[i % loose.length];
            src[i * 2] = s[0] + (r() - 0.5) * 0.03;
            src[i * 2 + 1] = s[1] + (r() - 0.5) * 0.03;
            const c = caps[s[2]];
            cap[i * 2] = c[0];
            cap[i * 2 + 1] = c[1];
            seed.set([r(), r(), r(), r()], i * 4);
        }
        const fg = new THREE.BufferGeometry();
        fg.setAttribute('position', new THREE.BufferAttribute(new Float32Array(N * 3), 3));
        fg.setAttribute('aSrc', new THREE.BufferAttribute(src, 2));
        fg.setAttribute('aCap', new THREE.BufferAttribute(cap, 2));
        fg.setAttribute('aSeed', new THREE.BufferAttribute(seed, 4));
        fg.boundingSphere = new THREE.Sphere(new THREE.Vector3(), 2);
        this.flow = new THREE.Points(
            fg,
            new THREE.ShaderMaterial({
                vertexShader: FLOW_VERT,
                fragmentShader: FLOW_FRAG,
                transparent: true,
                depthWrite: false,
                blending: THREE.AdditiveBlending,
                uniforms: {
                    uTime: { value: 0 }, uSize: { value: 9 }, uPixelRatio: { value: this.renderer.getPixelRatio() }, uFlow: { value: 1 }, uSpeed: { value: 0.045 },
                    uDhaka: { value: new THREE.Vector2(...dhaka) }, uMouse: { value: new THREE.Vector2(9, 9) }, uMouseOn: { value: 0 },
                    uMint: { value: C.mint }, uGreen: { value: C.green }, uGold: { value: C.gold }, uOpacity: { value: 0.42 },
                },
            }),
        );

        // --- Journey nodes: campus point → district ring → division node
        const districts = geo.districts.slice(0, Math.max(1, Math.min(opts.districts, geo.districts.length)));
        const M = geo.campuses.length;
        const aCampus = new Float32Array(M * 2), aRing = new Float32Array(M * 2), aNode = new Float32Array(M * 2), aSeed = new Float32Array(M);
        const members = districts.map(() => [] as number[]);
        geo.campuses.forEach((c, i) => {
            let best = 0;
            districts.forEach((d, k) => (Math.hypot(d[0] - c[0], d[1] - c[1]) < Math.hypot(districts[best][0] - c[0], districts[best][1] - c[1]) ? (best = k) : 0));
            members[best].push(i);
        });
        members.forEach((list, k) =>
            list.forEach((i, j) => {
                const a = (j / list.length) * Math.PI * 2;
                aRing.set([districts[k][0] + Math.cos(a) * 0.035, districts[k][1] + Math.sin(a) * 0.035], i * 2);
            }),
        );
        geo.campuses.forEach((c, i) => {
            aCampus.set([c[0], c[1]], i * 2);
            const cp = caps[c[2]];
            aNode.set([cp[0] + (r() - 0.5) * 0.012, cp[1] + (r() - 0.5) * 0.012], i * 2);
            aSeed[i] = r();
        });
        const ng = new THREE.BufferGeometry();
        ng.setAttribute('position', new THREE.BufferAttribute(new Float32Array(M * 3), 3));
        ng.setAttribute('aCampus', new THREE.BufferAttribute(aCampus, 2));
        ng.setAttribute('aRing', new THREE.BufferAttribute(aRing, 2));
        ng.setAttribute('aNode', new THREE.BufferAttribute(aNode, 2));
        ng.setAttribute('aSeed', new THREE.BufferAttribute(aSeed, 1));
        ng.boundingSphere = new THREE.Sphere(new THREE.Vector3(), 2);
        this.nodes = new THREE.Points(
            ng,
            new THREE.ShaderMaterial({
                vertexShader: NODE_VERT,
                fragmentShader: NODE_FRAG,
                transparent: true,
                depthWrite: false,
                blending: THREE.AdditiveBlending,
                uniforms: { uTime: { value: 0 }, uS1: { value: 0 }, uS2: { value: 0 }, uSize: { value: 26 }, uPixelRatio: { value: this.renderer.getPixelRatio() }, uMint: { value: C.mint }, uGold: { value: C.gold }, uOpacity: { value: 0 } },
            }),
        );

        // --- Outlines
        const loop = (ring: number[][], mat: THREE.LineBasicMaterial) => new THREE.LineLoop(new THREE.BufferGeometry().setFromPoints(ring.map(([x, y]) => new THREE.Vector3(x, y, 0))), mat);
        this.outline = loop(national, new THREE.LineBasicMaterial({ color: C.paper, transparent: true, opacity: 0.22, depthWrite: false }));
        geo.divisions.forEach((d) => this.divisionLines.push(loop(d.ring, new THREE.LineBasicMaterial({ color: C.mint, transparent: true, opacity: 0, depthWrite: false }))));

        // --- Kantha-stitched arcs into Dhaka (dashed = running stitch)
        caps.slice(1).forEach((c) => {
            const mid = new THREE.Vector3((c[0] + dhaka[0]) / 2, (c[1] + dhaka[1]) / 2, Math.hypot(c[0] - dhaka[0], c[1] - dhaka[1]) * 0.45);
            const curve = new THREE.QuadraticBezierCurve3(new THREE.Vector3(c[0], c[1], 0), mid, new THREE.Vector3(dhaka[0], dhaka[1], 0));
            const line = new THREE.Line(new THREE.BufferGeometry().setFromPoints(curve.getPoints(64)), new THREE.LineDashedMaterial({ color: C.gold, dashSize: 0.018, gapSize: 0.012, transparent: true, opacity: 0.95 }));
            line.computeLineDistances();
            line.geometry.setDrawRange(0, 0);
            this.arcs.push(line);
        });

        // --- Dhaka beacon
        this.beacon = new THREE.Mesh(
            new THREE.PlaneGeometry(0.34, 0.34),
            new THREE.ShaderMaterial({
                vertexShader: 'varying vec2 vUv; void main(){ vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.); }',
                fragmentShader: BEACON_FRAG,
                transparent: true,
                depthWrite: false,
                uniforms: { uTime: { value: 0 }, uSwell: { value: 0 }, uRed: { value: C.red }, uPaper: { value: C.paper } },
            }),
        );
        this.beacon.position.set(dhaka[0], dhaka[1], 0.006);

        this.nodePositions = caps.map(([x, y]) => new THREE.Vector3(x, y, 0));
        this.map.add(this.outline, ...this.divisionLines, this.flow, this.nodes, ...this.arcs, this.beacon);
        this.scene.add(this.map);
        this.resize();
    }

    resize() {
        const w = window.innerWidth, h = window.innerHeight;
        this.width = w;
        this.height = h;
        this.renderer.setSize(w, h, false);
        this.camera.aspect = w / h;
        this.camera.updateProjectionMatrix();
    }

    start() {
        if (this.raf) return;
        this.last = performance.now();
        const loop = (now: number) => {
            this.raf = requestAnimationFrame(loop);
            const dt = Math.min((now - this.last) / 1000, 0.05);
            this.last = now;
            if (!stage.visible.size || document.hidden) return; // paused off-screen
            this.elapsed += dt;
            this.frame(dt);
        };
        this.raf = requestAnimationFrame(loop);
    }

    stop() {
        cancelAnimationFrame(this.raf);
        this.raf = 0;
    }

    private frame(dt: number) {
        const t = this.elapsed;
        const s = stage.step;
        const flow = 1 - smooth(0, 0.5, s);
        const viewW = 2 * Math.tan(THREE.MathUtils.degToRad(17.5)) * this.camera.position.z * this.camera.aspect;

        // Pose: hero = large, right of the headline; journey = tilted map right of the copy.
        const heroX = viewW * 0.22, mapX = viewW * 0.19;
        this.map.position.x = THREE.MathUtils.lerp(mapX, heroX, flow);
        this.map.position.y = THREE.MathUtils.lerp(-0.02, -0.08, flow);
        this.map.scale.setScalar(THREE.MathUtils.lerp(0.8, 1.3, flow));

        // Damped pointer parallax, max 3 degrees.
        const max = THREE.MathUtils.degToRad(3);
        const p = stage.pointer;
        this.tilt.x += ((p.active ? -p.y : 0) * max - this.tilt.x) * Math.min(1, dt * 3);
        this.tilt.y += ((p.active ? p.x : 0) * max - this.tilt.y) * Math.min(1, dt * 3);
        this.map.rotation.set(THREE.MathUtils.lerp(-0.72, -0.55, flow) + this.tilt.x, this.tilt.y, THREE.MathUtils.lerp(0, -0.12, flow));
        this.map.updateMatrixWorld();

        // Cursor pushes particles like a hand in water: ray → map plane → local coords.
        const fu = this.flow.material.uniforms;
        if (p.active) {
            this.raycaster.setFromCamera(new THREE.Vector2(p.x, p.y), this.camera);
            this.plane.setFromNormalAndCoplanarPoint(new THREE.Vector3(0, 0, 1).applyQuaternion(this.map.quaternion), this.map.position);
            if (this.raycaster.ray.intersectPlane(this.plane, this.hit)) {
                this.map.worldToLocal(this.hit);
                (fu.uMouse.value as THREE.Vector2).set(this.hit.x, this.hit.y);
            }
        }
        fu.uMouseOn.value += ((p.active ? 1 : 0) - fu.uMouseOn.value) * Math.min(1, dt * 4);
        fu.uTime.value = t;
        fu.uFlow.value = flow;

        const nu = this.nodes.material.uniforms;
        nu.uTime.value = t;
        nu.uOpacity.value = smooth(0.3, 0.8, s);
        nu.uS1.value = smooth(1.5, 2.2, s);
        nu.uS2.value = smooth(2.5, 3.2, s);
        this.outline.material.opacity = THREE.MathUtils.lerp(0.4, 0.16, flow);
        const divA = smooth(2.5, 3.2, s) * 0.35;
        this.divisionLines.forEach((l) => (l.material.opacity = divA));
        const draw = smooth(3.4, 3.95, s);
        this.arcs.forEach((a, i) => a.geometry.setDrawRange(0, Math.floor(65 * smooth(i * 0.06, 0.6 + i * 0.06, draw))));

        const bu = this.beacon.material.uniforms;
        bu.uTime.value = t;
        bu.uSwell.value = smooth(3.5, 4, s);
        this.beacon.scale.setScalar(1 + bu.uSwell.value * 0.9);

        this.renderer.render(this.scene, this.camera);
        this.placeLabels(smooth(2.7, 3.2, s), smooth(3.7, 4, s));
        this.watchFps(dt);
    }

    /** Division labels + "NOV {edition}" follow their projected nodes; written straight to style, not Vue state. */
    private placeLabels(divA: number, beaconA: number) {
        const v = new THREE.Vector3();
        this.opts.labels.forEach((el, i) => {
            const isBeacon = i === this.opts.labels.length - 1;
            v.copy(this.nodePositions[isBeacon ? 0 : i]).applyMatrix4(this.map.matrixWorld).project(this.camera);
            const x = (v.x * 0.5 + 0.5) * this.width, y = (-v.y * 0.5 + 0.5) * this.height;
            el.style.transform = `translate3d(${x.toFixed(1)}px, ${(y + (isBeacon ? -54 : 10)).toFixed(1)}px, 0)`;
            el.style.opacity = String(isBeacon ? beaconA : divA);
        });
    }

    /** Drop to ~15k particles if the frame rate stays under 45fps for two seconds. */
    private watchFps(dt: number) {
        this.frames++;
        this.fpsWindow += dt;
        if (this.fpsWindow < 0.5) return;
        this.fps = this.frames / this.fpsWindow;
        this.slowFor = this.fps < 45 ? this.slowFor + this.fpsWindow : 0;
        this.frames = 0;
        this.fpsWindow = 0;
        if (!this.degraded && this.slowFor >= 2) {
            this.degraded = true;
            this.flow.geometry.setDrawRange(0, 15000);
            this.flow.material.uniforms.uOpacity.value = 0.7;
        }
    }

    dispose() {
        this.stop();
        this.scene.traverse((o) => {
            const m = o as THREE.Mesh;
            m.geometry?.dispose();
            (Array.isArray(m.material) ? m.material : [m.material]).forEach((mat) => mat?.dispose());
        });
        this.renderer.dispose();
        this.renderer.forceContextLoss();
    }
}
