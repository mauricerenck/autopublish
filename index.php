<?php

namespace mauricerenck\AutoPublish;

use Kirby\Cms\App as Kirby;

Kirby::plugin("mauricerenck/autopublish", [
    'blueprints' => require_once(__DIR__ . '/plugin/blueprints.php'),
    'routes' => require_once(__DIR__ . '/plugin/routes.php'),
    'hooks' => require_once(__DIR__ . '/plugin/hooks.php'),
]);
