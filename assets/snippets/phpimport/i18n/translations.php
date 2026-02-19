<?php

// File-based translation fallback.
// Keys mirror the legacy MySQL `translation` table columns (plus a few new keys).
// Locales are stored in lower-case, e.g. `fr-fr`, `nl-nl`.

return [
    // Default fallback
    'en-us' => [
        'subject' => 'Track & Trace',
        'sender' => 'Track & Trace',
        'customernumber' => 'Customer',

        'dear-customer-1' => 'Dear customer,',
        'dear-customer-2' => 'Please find below your Track & Trace overview.',
        'dear-customer-3' => 'Kind regards',

        'note' => '',

        'new-planned-delivery-dates' => 'Changed delivery dates',
        'new-planned-delivery-dates-text' => 'Orders with changed planned delivery dates.',
        'deliveries-in-the-coming-3-days' => 'Deliveries in the coming 3 days',
        'deliveries-in-the-coming-3-days-text' => 'Orders expected soon.',
        'all-open-orders' => 'All open orders',
        'all-open-orders-text' => 'Overview of open orders.',
        'new-orders' => 'New orders',
        'new-orders-text' => 'Newly created orders.',
        'shipped-orders' => 'Shipped orders',
        'shipped-orders-text' => 'Recently shipped orders.',

        'order-ref' => 'Order reference',
        'design' => 'Design',
        'order-date' => 'Order date',
        'planned-date' => 'Planned date',
        'end-consumer' => 'End consumer',
        'other-services' => 'Other services',

        'badge1-img' => '',
        'badge2-img' => '',
        'badge3-img' => '',
        'badge4-img' => '',
        'badge1-link' => '#',
        'badge2-link' => '#',
        'badge3-link' => '#',
        'badge4-link' => '#',

        'contact-tel-label' => 'Phone',
        'contact-email-label' => 'Email',
        'contact-tel' => '',
        'contact-email' => '',
        'opening' => '',
        'company-address' => '',

        'terms-of-use' => 'Terms of use',
        'terms-of-use-url' => '#',
        'privacy-notice' => 'Privacy notice',
        'privacy-notice-url' => '#',
        'unsubscribe' => 'Unsubscribe',
        'unsubscribe-url' => '/assets/snippets/phpimport/unsubscribe/unsubscribe.php?EmailAddress=',
        'footer' => '',

        // Seiko-specific blocks (tokenized template)
        'seiko-intro' => 'Dear customer, dear partner,<br><br>Please find below your daily report for tracking your Seiko lens orders. The information in this report is indicative. For any additional information, please contact Seiko Customer Service or your Strategic Lens Advisor.<br><br>The Seiko Optical France team',
        'seiko-footer-address' => '',
    ],

    // French (Seiko France + HOYA FR)
    'fr-fr' => [
        'subject' => 'Track & Trace',
        'sender' => 'Track & Trace',
        'customernumber' => 'Client',

        'dear-customer-1' => 'Cher(e) client(e),',
        'dear-customer-2' => 'Veuillez trouver ci-dessous votre aperçu Track & Trace.',
        'dear-customer-3' => 'Cordialement',

        'new-orders' => 'NOUVELLES COMMANDES',
        'new-orders-text' => "Retrouvez les commandes transmises aujourd'hui.",
        'new-planned-delivery-dates' => 'CHANGEMENT DE DÉLAIS',
        'new-planned-delivery-dates-text' => 'Retrouvez la liste des commandes dont la date de livraison a été modifiée.',
        'deliveries-in-the-coming-3-days' => 'LIVRAISONS SUR LES 3 PROCHAINS JOURS',
        'deliveries-in-the-coming-3-days-text' => 'Visualisez vos prochaines livraisons et organisez ainsi votre charge de travail en atelier.',
        'all-open-orders' => 'COMMANDES EN COURS',
        'all-open-orders-text' => 'Voici la liste des commandes Seiko validées et en cours de production.',

        'order-ref' => 'Référence de la commande',
        'design' => 'Design',
        'planned-date' => 'Date de livraison',
        'order-date' => 'Date de commande',
        'other-services' => 'AUTRES SERVICES SEIKO',

        'contact-tel-label' => 'Téléphone',
        'contact-email-label' => 'Email',
        'contact-tel' => '0 810 915 320',
        'contact-email' => 'service-clients.france@seikovision.com',

        'seiko-intro' => "Cher(e) client(e), cher(e) partenaire,<br><br>Vous retrouverez ci-dessous votre rapport quotidien du suivi de vos commandes de verres Seiko. Les informations mentionnées dans ce rapport sont indicatives. Pour toutes demandes d’informations complémentaires, n’hésitez pas à contacter votre Service Clients Seiko ou votre Conseiller Stratégique Verres.<br><br>L’équipe de Seiko Optical France",
        'seiko-footer-address' => 'Seiko Optical France<br>ZA PARIEST - Rue Willy Brandt<br>77184 EMERAINVILLE - France',
    ],

    // Dutch (placeholder – falls back to en-us for most keys)
    'nl-nl' => [
        'subject' => 'Track & Trace',
        'customernumber' => 'Klant',
    ],
];
