<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;

class EvTimerTest extends TimerTest {
    public function createLoop() {
        if (!Loop\EvDriver::isSupported()) {
            $this->markTestSkipped("EV extension required");
        }

        Loop::set(new Loop\EvDriver);
        return ReactAdapter::get();
    }
}
