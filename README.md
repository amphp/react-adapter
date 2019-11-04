# react-adapter

[![Build Status](https://img.shields.io/travis/amphp/react-adapter/master.svg?style=flat-square)](https://travis-ci.org/amphp/react-adapter)
[![Coverage Status](https://img.shields.io/coveralls/amphp/react-adapter/master.svg?style=flat-square)](https://coveralls.io/github/amphp/react-adapter?branch=master)
![Stable](https://img.shields.io/badge/stability-stable-green.svg?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

`amphp/react-adapter` makes any [ReactPHP](https://reactphp.org/) library compatible with [Amp](https://github.com/amphp/amp).

## Installation

```bash
composer require amphp/react-adapter
```

## Usage

Everywhere where a ReactPHP library requires an instance of `LoopInterface`, you just pass `ReactAdapter::get()` to run the ReactPHP library on Amp's event loop.

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

## Documentation

Documentation is available on [amphp.org/react-adapter](https://amphp.org/react-adapter/).

## Notes

If you need to convert an Amp promise to a ReactPHP promise, check the [umbri/amp-react-interop](https://github.com/umbri/amp-react-interop) package.
