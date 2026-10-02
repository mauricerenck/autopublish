<?php

namespace mauricerenck\AutoPublish;

use Kirby\Http\Response;

require_once __DIR__ . '/publish.php';

return [
    [
        'pattern' => 'autopublish/cron/(:any)',
        'method' => 'GET',
        /**
         * Webhook endpoint: publishes all due pages when called with the
         * secret configured via `mauricerenck.autopublish.secret`.
         */
        'action' => function (string $secret): Response {
            // onPageLoad already covers publishing; keep only one trigger active
            if (option('mauricerenck.autopublish.onPageLoad', false) === true) {
                return new Response('Forbidden', 'text/plain', 401);
            }

            if (option('mauricerenck.autopublish.secret', '') === $secret && $secret !== '') {
                publishDuePages();

                return new Response('OK', 'text/plain');
            }

            return new Response('Forbidden', 'text/plain', 401);
        }
    ],
];
