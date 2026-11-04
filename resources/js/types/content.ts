// Mirrors app/Content/*.php. Keep in sync when an admin panel replaces the arrays.

export interface Link {
    label: string;
    href: string;
}
export interface NavItem extends Link {
    group: string;
}
export type CtaTone = 'gold' | 'outline' | 'red' | 'green' | 'blue' | 'ghost';
export interface Cta extends Link {
    id: string;
    tone: CtaTone;
}
export interface Organiser {
    short: string;
    name: string;
    role: string;
    logo: string | null;
}

export interface Summit {
    name: string;
    edition: string;
    startsAt: string;
    dateConfirmed: boolean;
    city: string;
    venue: string | null;
    divisions: number;
    districts: number;
    campuses: number;
    tagline: string;
    taglineFinal: boolean;
    hashtag: string;
    social: Record<'facebook' | 'linkedin' | 'youtube' | 'x', string | null>;
}

export interface SiteData {
    navigation: NavItem[];
    navGroups: { id: string; label: string }[];
    headerLinks: Link[];
    ctas: { primary: Cta[]; secondary: Cta[] };
    organisers: Organiser[];
    contacts: { label: string; value: string | null }[];
    legal: Link[];
    importantLinks: Link[];
    url: string;
}

export interface SharedProps {
    locale: 'en' | 'bn';
    summit: Summit;
    site: SiteData;
    seo: { title?: string; description?: string; keywords?: string; image?: string } | null;
    flash: { success: string | null; submitted?: { form: string; reference: string } | null };
    errors: Record<string, string>;
    [key: string]: unknown;
}

export interface Hero {
    eyebrow: string;
    messages: string[];
    marquee: string[];
    video: { src: string; poster: string; srcMobile: string; posterMobile: string };
}
export interface PartnerGroup {
    label: string;
    slots: number;
    partners: { name: string; logo: string | null; href?: string }[];
}
export interface Trust {
    ticker: string[];
    groups: PartnerGroup[];
}
export interface About {
    eyebrow: string;
    headline: string;
    emphasis: string;
    intro: string;
    points: { title: string; icon: 'people' | 'ai' | 'globe' }[];
    cta: { label: string; href: string };
    image: string;
    video: string | null;
    baccoStats: { value: string; label: string }[] | null;
    baccoStatsSource: string;
}
export interface JourneyStep {
    count: number;
    title: string;
    tag: string;
    href: string;
    text: string;
}
export interface Division {
    name: string;
    lat: number;
    lng: number;
}
export interface Counter {
    id: string;
    label: string;
    value: number;
    of: number | null;
}
export interface DirectoryRow {
    type: 'division' | 'district' | 'campus';
    name: string;
    location: string | null;
    date: string | null;
    partner: string | null;
    href: string;
    image?: string | null;
}
export interface Journey {
    headline: string;
    headlineEmphasis: string;
    steps: JourneyStep[];
    beaconLabel: string;
    divisions: Division[];
    counters: Counter[];
    directory: { sample: boolean; rows: DirectoryRow[] };
}
export interface Audience {
    title: string;
    summary: string;
    icon: 'people' | 'rocket' | 'cap' | 'bulb' | 'building' | 'briefcase' | 'globe' | 'chart' | 'handshake';
    benefits: string[];
    cta: string;
    href: string;
}
export interface Highlight {
    title: string;
    text: string;
    image: string | null;
}
export interface Speaker {
    slug: string;
    role: string;
    name: string | null;
    designation: string | null;
    organisation: string | null;
    country: string | null;
    photo: string | null;
    bio: string | null;
    credential: string | null;
    expertise: string[];
    links: Link[];
    featured: boolean;
}
export interface Session {
    type: string;
    title: string;
    day: string;
    time: string;
    hall: string;
    speaker: string | null;
}
export interface Agenda {
    sample: boolean;
    types: string[];
    sessions: Session[];
}
export interface Awards {
    headline: string;
    emphasis: string;
    badge: string;
    categories: string[];
    categoriesProvisional: boolean;
    eligibility: string;
    statuses: string[];
    deadline: string | null;
}
export interface Exhibition {
    why: string[];
    who: string[];
    booths: { id: string; state: 'available' | 'reserved' | 'confirmed' }[];
    boothsIllustrative: boolean;
    enquiryFields: string[];
}
export interface Sponsorship {
    tiers: string[];
    provisional: boolean;
    prospectus: string | null;
}
export interface Forum {
    intro: string;
    purpose: string;
    eligibility: string;
    benefits: { title: string; text: string }[];
    faq: { q: string; a: string }[];
    portalNote: string;
}
export type StoryTile =
    | { kind: 'video'; role: string; youtubeId: string | null; poster?: string | null }
    | { kind: 'quote'; role: string; quote: string | null }
    | { kind: 'stat'; role: string; value: string | null; label: string };
export interface Stories {
    sample: boolean;
    tiles: StoryTile[];
    carousel: { programme: string; role: string; quote: string | null }[];
}
export interface Partners {
    provisional: boolean;
    tiers: { name: string; slots: number; sponsors: { name: string; logo: string | null; href?: string }[] }[];
}
export interface LatestNewsItem {
    title: string;
    text: string | null;
    href: string;
    tag: string;
    image: string;
}
export interface Media {
    tiles: { id: number; programme: string; kind: 'photo' | 'video'; src: string | null; caption: string | null }[];
    videos: { title: string; youtubeId: string | null }[];
}

export interface Heading {
    title: string;
    emphasis: string;
}
export type HeadingKey = 'why' | 'highlights' | 'speakers' | 'agenda' | 'exhibition' | 'sponsorship' | 'forum' | 'stories' | 'partners' | 'news' | 'media';

export interface HomeContent {
    headings: Record<HeadingKey, Heading>;
    hero: Hero;
    trust: Trust;
    about: About;
    journey: Journey;
    audiences: Audience[];
    highlights: Highlight[];
    speakers: Speaker[];
    agenda: Agenda;
    awards: Awards;
    exhibition: Exhibition;
    sponsorship: Sponsorship;
    forum: Forum;
    stories: Stories;
    partners: Partners;
    latestNews: LatestNewsItem[];
    media: Media;
    finalCta: { line: string; marquee: string };
}

export interface StubPage {
    title: string;
    description: string;
    upcoming: string;
    slug: string | null;
    path: string;
}
