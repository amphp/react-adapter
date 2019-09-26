<?php

namespace React\EventLoop;

use Amp\ReactAdapter\ReactAdapter;

/**
 * Class used to overwrite React's loop factory with an implementation returning the adaptor.
 *
 * @noinspection PhpUndefinedClassInspection
 */
final class Factory
{
    public static function create(): LoopInterface
    {
        return ReactAdapter::get();
    }
}
