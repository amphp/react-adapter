<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\Loop\NativeDriver;
use Amp\ReactAdapter\ReactAdapter;
use React\Tests\EventLoop\AbstractLoopTest;

class AdapterTest extends AbstractLoopTest {
    public function setUp() {
        Loop::set(new NativeDriver);

        parent::setUp();
    }

    public function createLoop() {
        return ReactAdapter::get();
    }
}
