<?php

// This example is adapted from reactphp/event-loop and shows the built-in protection against two active event loops
// https://github.com/reactphp/event-loop/blob/85a0b7c0e35a47387a61d2ba8a772a7855b6af86/examples/01-timers.php

require __DIR__ . '/../vendor/autoload.php';

// This will throw an error by default to ensure users are aware they're creating an additional event loop,
// which will probably happen by accident. Try running this script with AMP_REACT_ADAPTER_DISABLE_FACTORY_OVERRIDE=1
// to disable this protection, which will make this script run just fine.
$loop = React\EventLoop\Factory::create();

$loop->addTimer(0.8, function () {
    echo 'world!' . PHP_EOL;
});

$loop->addTimer(0.3, function () {
    echo 'hello ';
});

$loop->run();
