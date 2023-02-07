# amphp/react-adapter

![Stable](https://img.shields.io/badge/stability-stable-green.svg?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`amphp/react-adapter` makes any [ReactPHP](https://reactphp.org/) library compatible with [Amp](https://github.com/amphp/amp) v2.

> **Note**
> If you're using AMPHP v3, have a look at [`revolt/event-loop-adapter-react`](https://github.com/revoltphp/event-loop-adapter-react) instead.

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require amphp/react-adapter
```

## Usage

Everywhere where a ReactPHP library requires an instance of `LoopInterface`, you pass `ReactAdapter::get()` to run the ReactPHP library on Amp's event loop.

```php
<?php

require 'vendor/autoload.php';

use Amp\Loop;
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
