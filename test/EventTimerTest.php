<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;
use React\EventLoop\LoopInterface;

class EventTimerTest extends TimerTest
{
    public function createLoop(): LoopInterface
    {
        if (!Loop\EventDriver::isSupported()) {
            $this->markTestSkipped("Event extension required");
        }

        Loop::set(new Loop\EventDriver);
        return ReactAdapter::get();
    }
}
