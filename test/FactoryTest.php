<?php

namespace Amp\ReactAdapter\Test;

use Amp\ReactAdapter\ReactAdapter;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;

class FactoryTest extends TestCase
{
    public function testFactoryReturnsAdaptor()
    {
        \define(Factory::SILENCE_CONST_NAME, true);
        $loop = Factory::create();
        $this->assertInstanceOf(ReactAdapter::class, $loop);
    }
}
