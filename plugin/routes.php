<?php

namespace mauricerenck\AutoPublish;

use Kirby\Http\Response;

return [
    [
        'pattern' => 'autopublish/cron/(:any)',
        'method' => 'GET',
        'action' => function ($secret) {
            if (option('mauricerenck.autopublish.secret', '') === $secret && $secret !== '') {
                $dateField = option('mauricerenck.autopublish.dateField', 'autopublishDate');

                $unpublishedPages = kirby()->site()->index()->drafts()->filter(function ($page) use ($dateField) {
                    // an empty date field's toDate() is null, and `null <= time()`
                    // is true in PHP, so without this check every draft with the
                    // toggle on but no date yet would be treated as due
                    $date = $page->$dateField();
                    return $date->isNotEmpty() && $date->toDate() <= time();
                })->filterBy('autopublish', '==', true);

                kirby()->impersonate('kirby');
                foreach ($unpublishedPages as $page) {
                    try {
                        $page->changeStatus('listed');
                    } catch (\Throwable $e) {
                        // one draft failing (e.g. missing required fields) must
                        // not block every other due page from being published
                    }
                }

                return new Response('OK', 'text/plain');
            }

            return new Response('Forbidden', 'text/plain', 401);
        }
    ],
];
