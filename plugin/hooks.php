<?php

namespace mauricerenck\AutoPublish;

use Kirby\Cms\Page;

return [
    /**
     * Runs the due-pages scan before a page is rendered on the frontend,
     * gated behind `mauricerenck.autopublish.onPageLoad`. Lets a due page
     * publish itself before the visitor requesting it sees the response.
     */
    'page.render:before' => function (string $_contentType, array $data, Page $_page): array {
        if (option('mauricerenck.autopublish.onPageLoad', false) === false) {
            return $data;
        }

        $publisher = new Publisher();

        if ($publisher->shouldCheckForDuePages()) {
            $publisher->publishDuePages();
        }

        return $data;
    }
];
