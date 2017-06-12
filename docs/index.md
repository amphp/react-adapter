---
title: Adapter for ReactPHP's Event Loop
permalink: /
---
`amphp/react-adapter` makes any ReactPHP library work with Amp.

## Installation

```
composer require amphp/react-adapter
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use Amp\ReactAdapter\ReactAdapter;

Loop::run(function () {
    $app = function ($request, $response) {
        $response->writeHead(200, array('Content-Type' => 'text/plain'));
        $response->end("Hello World\n");
    };

    $socket = new React\Socket\Server(ReactAdapter::get());
    $http = new React\Http\Server($socket, ReactAdapter::get());

    $http->on('request', $app);
    echo "Server running at http://127.0.0.1:1337\n";

    $socket->listen(1337);
});
```

Everywhere where a ReactPHP library requires an instance of `LoopInterface`, you just pass `ReactAdapter::get()` to run the ReactPHP library on the Amp event loop.
