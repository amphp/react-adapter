<?php

namespace Amp\ReactAdapter;

use Amp\Loop;
use Amp\Loop\Driver;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use React\EventLoop\TimerInterface;

class ReactAdapter implements LoopInterface {
    private $driver;

    private $readWatchers = [];
    private $writeWatchers = [];
    private $timers = [];
    private $signalWatchers = [];

    public function __construct(Driver $driver) {
        $this->driver = $driver;
    }

    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param resource $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, $listener) {
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
    public function addWriteStream($stream, $listener) {
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
    public function addTimer($interval, $callback) {
        $timer = new Timer($interval, $callback, false);

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
    public function addPeriodicTimer($interval, $callback) {
        $timer = new Timer($interval, $callback, true);

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
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function futureTick($listener) {
        $this->driver->defer(function () use ($listener) {
            $listener($this);
        });
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

    /**
     * Register a listener to be notified when a signal has been caught by this process.
     *
     * This is useful to catch user interrupt signals or shutdown signals from
     * tools like `supervisor` or `systemd`.
     *
     * The listener callback function MUST be able to accept a single parameter,
     * the signal added by this method or you MAY use a function which
     * has no parameters at all.
     *
     * The listener callback function MUST NOT throw an `Exception`.
     * The return value of the listener callback function will be ignored and has
     * no effect, so for performance reasons you're recommended to not return
     * any excessive data structures.
     *
     * ```php
     * $loop->addSignal(SIGINT, function (int $signal) {
     *     echo 'Caught user interrupt signal' . PHP_EOL;
     * });
     * ```
     *
     * See also [example #4](examples).
     *
     * Signaling is only available on Unix-like platform, Windows isn't
     * supported due to operating system limitations.
     * This method may throw a `BadMethodCallException` if signals aren't
     * supported on this platform, for example when required extensions are
     * missing.
     *
     * **Note: A listener can only be added once to the same signal, any
     * attempts to add it more then once will be ignored.**
     *
     * @param int $signal
     * @param callable $listener
     *
     * @throws \BadMethodCallException when signals aren't supported on this
     *     platform, for example when required extensions are missing.
     *
     * @return void
     */
    public function addSignal($signal, $listener) {
        if (($watcherId = $this->getSignalWatcherId($signal, $listener)) !== false) {
            // do not add the signal handler more than once
            return;
        }

        try {
            $watcherId = $this->driver->onSignal($signal, $listener);
            $this->signalWatchers[$watcherId] = [$signal, $listener];
        } catch (Loop\UnsupportedFeatureException $e) {
            throw new \BadMethodCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Removes a previously added signal listener.
     *
     * ```php
     * $loop->removeSignal(SIGINT, $listener);
     * ```
     *
     * Any attempts to remove listeners that aren't registered will be ignored.
     *
     * @param int $signal
     * @param callable $listener
     *
     * @return void
     */
    public function removeSignal($signal, $listener) {
        if (($watcherId = $this->getSignalWatcherId($signal, $listener)) === false) {
            // the signal handler is not registered
            return;
        }

        $this->driver->unreference($watcherId);

        unset($this->signalWatchers[$watcherId]);
    }

    public static function get(): LoopInterface {
        if ($loop = Loop::getState(self::class)) {
            return $loop;
        }

        Loop::setState(self::class, $loop = new self(Loop::get()));

        return $loop;
    }

    /**
     * Gets the Amp watcher id for the signal handler.
     *
     * @param int $signal
     * @param callable $listener
     *
     * @return string|false
     */
    private function getSignalWatcherId(int $signal, callable $listener) {
        return array_search([$signal, $listener], $this->signalWatchers);
    }
}
