<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;

// Bug report: https://github.com/bwoebi/php-uv/issues/35

class UvTest extends Test {
    public function createLoop() {
        if (!Loop\UvDriver::isSupported()) {
            $this->markTestSkipped("UV extension required");
        }

        Loop::set(new Loop\UvDriver);
        return ReactAdapter::get();
    }

    public function testAddReadStream() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testAddWriteStream() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRemoveReadStreamAfterReading() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRemoveWriteStreamAfterWriting() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRemoveStreamForReadOnly() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRemoveStreamForWriteOnly() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRemoveStream() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function runShouldReturnWhenNoMoreFds() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function stopShouldStopRunningLoop() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testIgnoreRemovedCallback() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testNextTickFiresBeforeIO() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRecursiveNextTick() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRunWaitsForNextTickEvents() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testFutureTickFiresBeforeIO() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRecursiveFutureTick() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }

    public function testRunWaitsForFutureTickEvents() {
        $this->markTestSkipped("Memory streams are currently unsupported");
    }
}