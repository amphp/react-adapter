<?php

// This example is adapted from reactphp/event-loop, but running on Amp's event loop.
// https://github.com/reactphp/event-loop/blob/85a0b7c0e35a47387a61d2ba8a772a7855b6af86/examples/01-timers.php

use Amp\ReactAdapter\ReactAdapter;

require __DIR__ . '/../vendor/autoload.php';

$loop = ReactAdapter::get();

$loop->addTimer(0.8, function () {
    echo 'world!' . PHP_EOL;
});

$loop->addTimer(0.3, function () {
    echo 'hello ';
});

$loop->run();
