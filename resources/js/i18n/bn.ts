import type { Messages } from './en';

type DeepPartial<T> = { [K in keyof T]?: T[K] extends object ? DeepPartial<T[K]> : T[K] };

// Seeded strings only. Every other key falls back to English and is pending native review:
// NATIVE-REVIEW: header.*, countdown.*, common.*, sections.*, form.*, page.*, inner.*
const bn: DeepPartial<Messages> = {
    brand: {
        title: 'ফ্রিল্যান্সার সামিট বাংলাদেশ',
        titleLines: ['ফ্রিল্যান্সার', 'সামিট', 'বাংলাদেশ'],
    },
    cta: { register: 'নিবন্ধন করুন' },
};

export default bn;
