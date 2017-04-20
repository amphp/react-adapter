<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;

class UvTimerTest extends TimerTest {
    public function createLoop() {
        if (!Loop\UvDriver::isSupported()) {
            $this->markTestSkipped("UV extension required");
        }

        Loop::set(new Loop\UvDriver);
        return ReactAdapter::get();
    }
}