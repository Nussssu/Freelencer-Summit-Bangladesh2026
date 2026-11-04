// Shared control styling (matches the landing's enquiry form inputs).
export const inputClass = 'block min-h-11 w-full rounded-lg border bg-snow px-3.5 py-2.5 text-night shadow-[0_1px_2px_rgb(7_20_17/0.04)] transition-[border-color,box-shadow] duration-200 outline-none focus:ring-3';
export const stateClass = (invalid: boolean) => (invalid ? 'border-red focus:ring-red/15' : 'border-night/15 hover:border-night/30 focus:border-green focus:ring-green/15');

// The registration mock-ups' taller white control (phone fields add the +880 prefix at left-4).
export const iconInputClass = (invalid: boolean) => [
    'block min-h-12 w-full rounded-lg border bg-white px-4 font-sans text-night shadow-[0_1px_2px_rgb(7_20_17/0.04)] transition-[border-color,box-shadow] duration-200 outline-none placeholder:text-ink-2/80 focus:ring-3',
    stateClass(invalid),
];
