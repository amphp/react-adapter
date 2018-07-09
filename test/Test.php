<?php

namespace Amp\ReactAdapter\Test;

use Amp\Loop;
use Amp\ReactAdapter\ReactAdapter;
use React\Tests\EventLoop\AbstractLoopTest;

class Test extends AbstractLoopTest {
    private $fifoPath;

    public function tearDown() {
        if (file_exists($this->fifoPath)) {
            unlink($this->fifoPath);
        }
    }

    public function createStream() {
        // Ev: No report (https://bitbucket.org/osmanov/pecl-ev/issues)
        // Event: Won't fix (https://bitbucket.org/osmanov/pecl-event/issues/2/add-support-of-php-temp)
        // Uv: Open (https://github.com/bwoebi/php-uv/issues/35)

        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            return parent::createStream();
        }

        $this->fifoPath = tempnam(sys_get_temp_dir(), "amphp-react-adapter-");

        unlink($this->fifoPath);
        posix_mkfifo($this->fifoPath, 0600);

        return fopen($this->fifoPath, 'r+');
    }

    public function writeToStream($stream, $content) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            parent::writeToStream($stream, $content);
        }

        fwrite($stream, $content);
    }

    public function createLoop() {
        Loop::set(new Loop\NativeDriver);
        return ReactAdapter::get();
    }

    public function testIgnoreRemovedCallback() {
        // We don't have the order guarantee, so we recreate this test
        // and accept that one handler is called, but not the other.

        $stream1 = $this->createStream();
        $stream2 = $this->createStream();

        $stream1called = false;
        $stream2called = false;

        $this->loop->addReadStream($stream1, function () use (&$stream1called, $stream1, $stream2) {
            $stream1called = true;

            $this->loop->removeReadStream($stream1);
            $this->loop->removeReadStream($stream2);
        });

        $this->loop->addReadStream($stream1, function () use (&$stream2called, $stream1, $stream2) {
            $stream2called = true;

            $this->loop->removeReadStream($stream1);
            $this->loop->removeReadStream($stream2);
        });

        $this->writeToStream($stream1, "foo\n");
        $this->writeToStream($stream2, "foo\n");

        $this->loop->run();

        $this->assertTrue((bool) ($stream1called ^ $stream2called));
    }
}
