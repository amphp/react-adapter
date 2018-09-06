<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;
use React\EventLoop\LoopInterface;

class UvTest extends Test {
    public function createLoop(): LoopInterface {
        if (!Loop\UvDriver::isSupported()) {
            $this->markTestSkipped("UV extension required");
        }

        Loop::set(new Loop\UvDriver);
        return ReactAdapter::get();
    }
}
