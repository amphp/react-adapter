---
title: Adapter for ReactPHP's Event Loop
permalink: /
---
`amphp/react-adapter` makes any [ReactPHP](https://reactphp.org/) library work with [Amp](https://amphp.org/amp/).

## Installation

```
composer require amphp/react-adapter
```

## Usage

Everywhere where a ReactPHP library requires an instance of `LoopInterface`, you just pass `ReactAdapter::get()` to run the ReactPHP library on the Amp event loop.

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

You can also use the adapter to run ReactPHP apps on an Amp event loop implementation without relying on Amp's global event loop.

```php
$loop = new Amp\ReactAdapter\ReactAdapter((new Amp\Loop\DriverFactory)->create());
```
