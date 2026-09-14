<?php

namespace mauricerenck\AutoPublish;

use Kirby\Cms\Page;

require_once __DIR__ . '/publish.php';

return [
    /**
     * Runs the due-pages scan before a page is rendered on the frontend,
     * gated behind `mauricerenck.autopublish.onPageLoad`. Lets a due page
     * publish itself before the visitor requesting it sees the response.
     */
    'page.render:before' => function (string $contentType, array $data, Page $page): array {
        if (option('mauricerenck.autopublish.onPageLoad', false) && shouldCheckForDuePages()) {
            publishDuePages();
        }

        return $data;
    }
];
