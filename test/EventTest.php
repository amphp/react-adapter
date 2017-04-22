<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;

// Bug report: None

class EventTest extends Test {
    public function createLoop() {
        if (!Loop\EventDriver::isSupported()) {
            $this->markTestSkipped("Event extension required");
        }

        Loop::set(new Loop\EventDriver);
        return ReactAdapter::get();
    }
}