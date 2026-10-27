import { createI18n } from 'vue-i18n';
import en from './en';
import bn from './bn';

export type Locale = 'en' | 'bn';

export function makeI18n(locale: Locale) {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'en',
        messages: { en, bn: bn as typeof en },
        missingWarn: false,
        fallbackWarn: false,
    });
}

const BN_DIGITS = '০১২৩৪৫৬৭৮৯';
export const toBanglaDigits = (n: number | string) => String(n).replace(/\d/g, (d) => BN_DIGITS[+d]);
