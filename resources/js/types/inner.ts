// Prop shapes for inner pages. Mirrors app/Queries/* (PHP is the source of truth).
import type { About, Agenda, Audience, Awards, CtaTone, Highlight, Journey, Session } from './content';

export interface Crumb {
    label: string;
    href?: string;
}
export interface Option {
    value: string;
    label: string;
    description?: string;
    disabled?: boolean;
}
export interface OptionGroup {
    label: string;
    options: Option[];
}
/** Built main-navigation page (App\Content\InnerPageContent via InnerPageController). */
export interface InnerPage {
    title: string;
    description: string;
    path: string;
    crumbs: Crumb[];
    hero: { eyebrow: string; title: string; emphasis: string; intro: string; actions: { label: string; href: string; tone: CtaTone }[] };
}
export interface SectionHeading {
    eyebrow: string;
    title: string;
    emphasis: string | null;
    intro: string | null;
}
type Headings<K extends string> = Record<K, SectionHeading>;

export interface AboutContent {
    headings: Headings<'why' | 'objectives' | 'phases' | 'audiences' | 'highlights' | 'organisers' | 'cta'>;
    about: About & { statement: string; highlight: string; paragraphs: string[]; stamp: string };
    objectives: string[];
    phases: { title: string; coverage: string; href: string; activities: string[] }[];
    audiences: Audience[];
    highlights: Highlight[];
}
export interface JourneyContent {
    headings: Headings<'progress' | 'route' | 'divisions' | 'includes' | 'cta'>;
    journey: Journey;
    reel: { image: string; place: string; date: string }[];
    divisionSummits: { name: string; date: string; venue: string; districts: number; campuses: number; participants: number; held: boolean; image: string }[];
    includes: { title: string; text: string }[];
}
export interface AgendaContent {
    headings: Headings<'programme' | 'formats' | 'downloads' | 'cta'>;
    agenda: Omit<Agenda, 'sessions'> & { sessions: (Session & { ics: string })[] };
    formats: { title: string; text: string }[];
    downloads: DownloadItem[];
}
export interface SpeakersContent {
    headings: Headings<'directory' | 'categories' | 'cta'>;
    speakers: SpeakerCard[];
    categories: string[];
}
export interface AwardsContent {
    headings: Headings<'overview' | 'categories' | 'steps' | 'statuses' | 'shortlist' | 'winners' | 'faq' | 'cta'>;
    awards: Awards;
    facts: { label: string; value: string }[];
    categoryNote: string;
    shortlistEmpty: { title: string; body: string };
    winnersEmpty: { title: string; body: string };
    steps: { title: string; text: string }[];
    faq: FaqItem[];
    downloads: DownloadItem[];
}

export interface PageMeta {
    page: number;
    lastPage: number;
    total: number;
    perPage: number;
}
export interface Paginated<T> {
    data: T[];
    meta: PageMeta;
}
export interface SpeakerCard {
    slug: string;
    name: string;
    designation: string | null;
    organisation: string | null;
    country: string;
    role: string | null;
    photo: string | null;
    expertise: string[];
    featured: boolean;
    sample: boolean;
    href: string;
}
export interface SessionCard {
    slug: string;
    title: string;
    type: string;
    typeLabel: string;
    date: string | null;
    start: string | null;
    end: string | null;
    hall: string | null;
    track: string | null;
    speakers: { name: string; href: string }[];
    sample: boolean;
    href: string;
}
export interface ProgramCard {
    slug: string;
    type: string;
    title: string;
    startsAt: string | null;
    location: string | null;
    partner: string | null;
    division: string | null;
    district: string | null;
    status: string;
    statusLabel: string;
    registrationOpen: boolean;
    image: string | null;
    sample: boolean;
    href: string;
}
export interface ArticleCard {
    slug: string;
    title: string;
    excerpt: string | null;
    category: string;
    categoryLabel: string;
    publishedAt: string | null;
    image: string | null;
    sample: boolean;
    href: string;
}
export interface ExhibitorCard {
    slug: string;
    name: string;
    sector: string;
    logo: string | null;
    booth: string | null;
    sample: boolean;
    href: string;
}
export interface Tier {
    slug: string;
    name: string;
    benefits: string[];
    provisional: boolean;
}
export interface DownloadItem {
    kind: string;
    title: string;
    href: string | null;
    size: number | null;
}
export interface FaqItem {
    q: string;
    a: string;
    sample?: boolean;
}
export interface Consent {
    version: string;
    text: string;
}
export interface LinkRow {
    platform?: string;
    url: string;
}
