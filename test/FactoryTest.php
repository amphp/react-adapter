<?php

namespace Amp\ReactAdapter\Test;

use Amp\ReactAdapter\ReactAdapter;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;

class FactoryTest extends TestCase
{
    public function testFactoryReturnsAdaptor()
    {
        $loop = Factory::create();
        $this->assertInstanceOf(ReactAdapter::class, $loop);
    }
}
