<?php

namespace Amp\ReactAdapter;

use React\EventLoop\TimerInterface;

class Timer implements TimerInterface
{
    /** @var float */
    private $interval;

    /** @var callable */
    private $callback;

    /** @var bool */
    private $periodic;

    public function __construct(float $interval, callable $callback, bool $periodic = false)
    {
        if ($interval < 0.000001) {
            $interval = 0.000001;
        }

        $this->interval = $interval;
        $this->callback = $callback;
        $this->periodic = $periodic;
    }

    /** @inheritdoc */
    public function getInterval(): float
    {
        return $this->interval;
    }

    /** @inheritdoc */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /** @inheritdoc */
    public function isPeriodic(): bool
    {
        return $this->periodic;
    }
}
