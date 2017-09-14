<?php

namespace Amp\ReactAdapter;

use Amp\Loop;
use Amp\Loop\Driver;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use React\EventLoop\Timer\TimerInterface;

class ReactAdapter implements LoopInterface {
    private $driver;

    private $inNextTick = false;
    private $readWatchers = [];
    private $writeWatchers = [];
    private $timers = [];

    public function __construct(Driver $driver) {
        $this->driver = $driver;
    }

    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param resource $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, callable $listener) {
        if (isset($this->readWatchers[(int) $stream])) {
            // Double watchers are silently ignored by ReactPHP
            return;
        }

        $watcher = $this->driver->onReadable($stream, function () use ($stream, $listener) {
            $listener($stream, $this);
        });

        $this->readWatchers[(int) $stream] = $watcher;
    }

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param resource $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addWriteStream($stream, callable $listener) {
        if (isset($this->writeWatchers[(int) $stream])) {
            // Double watchers are silently ignored by ReactPHP
            return;
        }

        $watcher = $this->driver->onWritable($stream, function () use ($stream, $listener) {
            $listener($stream, $this);
        });

        $this->writeWatchers[(int) $stream] = $watcher;
    }

    /**
     * Remove the read event listener for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeReadStream($stream) {
        $key = (int) $stream;

        if (!isset($this->readWatchers[$key])) {
            return;
        }

        $this->driver->cancel($this->readWatchers[$key]);

        unset($this->readWatchers[$key]);
    }

    /**
     * Remove the write event listener for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeWriteStream($stream) {
        $key = (int) $stream;

        if (!isset($this->writeWatchers[$key])) {
            return;
        }

        $this->driver->cancel($this->writeWatchers[$key]);

        unset($this->writeWatchers[$key]);
    }

    /**
     * Remove all listeners for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeStream($stream) {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);
    }

    /**
     * Enqueue a callback to be invoked once after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable  $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback) {
        $timer = new Timer($this, $interval, $callback, false);

        $watcher = $this->driver->delay((int) (1000 * $interval), function () use ($timer, $callback) {
            $this->cancelTimer($timer);

            $callback($timer);
        });

        $this->timers[spl_object_hash($timer)] = $watcher;

        return $timer;
    }

    /**
     * Enqueue a callback to be invoked repeatedly after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable  $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback) {
        $timer = new Timer($this, $interval, $callback, true);

        $watcher = $this->driver->repeat((int) (1000 * $interval), function () use ($timer, $callback) {
            $callback($timer);
        });

        $this->timers[spl_object_hash($timer)] = $watcher;

        return $timer;
    }

    /**
     * Cancel a pending timer.
     *
     * @param TimerInterface $timer The timer to cancel.
     */
    public function cancelTimer(TimerInterface $timer) {
        if (!isset($this->timers[spl_object_hash($timer)])) {
            return;
        }

        $this->driver->cancel($this->timers[spl_object_hash($timer)]);

        unset($this->timers[spl_object_hash($timer)]);
    }

    /**
     * Check if a given timer is active.
     *
     * @param TimerInterface $timer The timer to check.
     *
     * @return boolean True if the timer is still enqueued for execution.
     */
    public function isTimerActive(TimerInterface $timer) {
        return isset($this->timers[spl_object_hash($timer)]);
    }

    /**
     * Schedule a callback to be invoked on the next tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued,
     * before any timer or stream events.
     *
     * @param callable $listener The callback to invoke.
     */
    public function nextTick(callable $listener) {
        if ($this->inNextTick) {
            $listener($this);

            return;
        }

        $this->driver->defer(function () use ($listener) {
            $previousValue = $this->inNextTick;
            $this->inNextTick = true;

            try {
                $listener($this);
            } finally {
                $this->inNextTick = $previousValue;
            }
        });
    }

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function futureTick(callable $listener) {
        $this->driver->defer(function () use ($listener) {
            $listener($this);
        });
    }

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick() {
        $this->driver->defer(function () {
            $this->driver->stop();
        });

        $this->run();
    }

    /**
     * Run the event loop until there are no more tasks to perform.
     */
    public function run() {
        $this->driver->run();
    }

    /**
     * Instruct a running event loop to stop.
     */
    public function stop() {
        $this->driver->stop();
    }

    public static function get(): LoopInterface {
        if ($loop = Loop::getState(self::class)) {
            return $loop;
        }

        Loop::setState(self::class, $loop = new self(Loop::get()));

        return $loop;
    }
}
