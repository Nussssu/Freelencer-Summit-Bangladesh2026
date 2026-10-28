<?php

/*
| Versioned consent wording. Each submission stores the version it agreed to.
| Draft wording until the organiser supplies approved text; add a new version, never edit an old one.
*/

return [
    'version' => env('CONSENT_VERSION', '2026-10-draft'),

    'texts' => [
        '2026-10-draft' => 'I agree that BACCO and the Freelancer Summit Bangladesh team may store the details in this form to handle my request and contact me about it, as described in the Privacy Policy.',
    ],

    // Organiser inbox for contact-form copies.
    'organiser_email' => env('SUMMIT_ORGANISER_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
];
