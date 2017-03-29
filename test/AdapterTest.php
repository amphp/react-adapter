<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\Loop\NativeDriver;
use Amp\ReactAdapter\ReactAdapter;
use React\Tests\EventLoop\AbstractLoopTest;

class AdapterTest extends AbstractLoopTest {
    public function setUp() {
        Loop::set(new NativeDriver);

        // FIXME: Remove once https://github.com/reactphp/event-loop/pull/92 is merged
        \define("HHVM_VERSION", true);

        parent::setUp();
    }

    public function createLoop() {
        return ReactAdapter::get();
    }
}
