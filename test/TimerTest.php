<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\Loop\NativeDriver;
use Amp\ReactAdapter\ReactAdapter;
use React\Tests\EventLoop\Timer\AbstractTimerTest;

class TimerTest extends AbstractTimerTest {
    public function createLoop() {
        Loop::set(new NativeDriver);
        return ReactAdapter::get();
    }
}
