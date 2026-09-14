<?php

namespace mauricerenck\AutoPublish;

use Kirby\Cache\Cache;
use Kirby\Cms\Page;

/**
 * Whether the due-pages scan is allowed to run right now.
 *
 * Throttles publishDuePages() to at most once per
 * `onPageLoadInterval` seconds, using Kirby's cache to remember the
 * last run across requests (each request is a fresh PHP process).
 */
function shouldCheckForDuePages(): bool
{
    $interval = (int) option('mauricerenck.autopublish.onPageLoadInterval', 60);

    if ($interval <= 0) {
        return true;
    }

    /** @var Cache $cache */
    $cache = kirby()->cache('mauricerenck.autopublish');
    $lastCheck = $cache->get('last-check');

    if ($lastCheck !== null && time() - $lastCheck < $interval) {
        return false;
    }

    // cache TTL is in whole minutes; round up so a short interval
    // (e.g. 10s) isn't rounded down to 0 and cached forever
    $cache->set('last-check', time(), (int) ceil($interval / 60) + 1);

    return true;
}

/**
 * Publishes every draft whose autopublish date is due.
 *
 * Impersonates the kirby user so this also works outside of an
 * authenticated panel session (webhook call, frontend page load).
 */
function publishDuePages(): void
{
    $dateField = option('mauricerenck.autopublish.dateField', 'autopublishDate');

    $unpublishedPages = kirby()->site()->index()->drafts()->filter(function (Page $page) use ($dateField): bool {
        $date = $page->$dateField();

        // an empty date field's toDate() is null, and `null <= time()`
        // is true in PHP, so without this check every draft with the
        // toggle on but no date yet would be treated as due
        return $date->isNotEmpty() && $date->toDate() <= time();
    })->filterBy('autopublish', '==', true);

    kirby()->impersonate('kirby');

    /** @var Page $page */
    foreach ($unpublishedPages as $page) {
        try {
            $page->changeStatus('listed');
        } catch (\Throwable $e) {
            // one draft failing (e.g. missing required fields) must not
            // block every other due page from being published
        }
    }
}
