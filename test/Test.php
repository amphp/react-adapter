<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;
use React\Tests\EventLoop\AbstractLoopTest;

class Test extends AbstractLoopTest {
    public function setUp() {
        // FIXME: Remove once https://github.com/reactphp/event-loop/pull/92 is merged
        if (!\defined("HHVM_VERSION")) {
            \define("HHVM_VERSION", true);
        }

        parent::setUp();
    }

    public function createLoop() {
        Loop::set(new Loop\NativeDriver);
        return ReactAdapter::get();
    }
}
