<?php

namespace Amp\ReactAdapter;

use Amp\Loop;
use Amp\Loop\Driver;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class ReactAdapter implements LoopInterface
{
    private $driver;

    private $readWatchers = [];
    private $writeWatchers = [];
    private $timers = [];
    private $signals = [];

    public static function get(): LoopInterface
    {
        if ($loop = Loop::getState(self::class)) {
            return $loop;
        }

        Loop::setState(self::class, $loop = new self(Loop::get()));

        return $loop;
    }

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /** @inheritdoc */
    public function addReadStream($stream, $listener)
    {
        if (isset($this->readWatchers[(int) $stream])) {
            // Double watchers are silently ignored by ReactPHP
            return;
        }

        $watcher = $this->driver->onReadable($stream, static function () use ($stream, $listener) {
            $listener($stream);
        });

        $this->readWatchers[(int) $stream] = $watcher;
    }

    /** @inheritdoc */
    public function addWriteStream($stream, $listener)
    {
        if (isset($this->writeWatchers[(int) $stream])) {
            // Double watchers are silently ignored by ReactPHP
            return;
        }

        $watcher = $this->driver->onWritable($stream, static function () use ($stream, $listener) {
            $listener($stream);
        });

        $this->writeWatchers[(int) $stream] = $watcher;
    }

    /** @inheritdoc */
    public function removeReadStream($stream)
    {
        $key = (int) $stream;

        if (!isset($this->readWatchers[$key])) {
            return;
        }

        $this->driver->cancel($this->readWatchers[$key]);

        unset($this->readWatchers[$key]);
    }

    /** @inheritdoc */
    public function removeWriteStream($stream)
    {
        $key = (int) $stream;

        if (!isset($this->writeWatchers[$key])) {
            return;
        }

        $this->driver->cancel($this->writeWatchers[$key]);

        unset($this->writeWatchers[$key]);
    }

    /** @inheritdoc */
    public function addTimer($interval, $callback): TimerInterface
    {
        $timer = new Timer($interval, $callback, false);

        $watcher = $this->driver->delay((int) \ceil(1000 * $timer->getInterval()), function () use ($timer, $callback) {
            $this->cancelTimer($timer);

            $callback($timer);
        });

        $this->deferEnabling($watcher);
        $this->timers[\spl_object_hash($timer)] = $watcher;

        return $timer;
    }

    /** @inheritdoc */
    public function addPeriodicTimer($interval, $callback): TimerInterface
    {
        $timer = new Timer($interval, $callback, true);

        $watcher = $this->driver->repeat((int) \ceil(1000 * $timer->getInterval()), function () use ($timer, $callback) {
            $callback($timer);
        });

        $this->deferEnabling($watcher);
        $this->timers[\spl_object_hash($timer)] = $watcher;

        return $timer;
    }

    /** @inheritdoc */
    public function cancelTimer(TimerInterface $timer)
    {
        if (!isset($this->timers[\spl_object_hash($timer)])) {
            return;
        }

        $this->driver->cancel($this->timers[\spl_object_hash($timer)]);

        unset($this->timers[\spl_object_hash($timer)]);
    }

    /** @inheritdoc */
    public function futureTick($listener)
    {
        $this->driver->defer(static function () use ($listener) {
            $listener();
        });
    }

    /** @inheritdoc */
    public function addSignal($signal, $listener)
    {
        if (\in_array($listener, $this->signals[$signal] ?? [], true)) {
            return;
        }

        try {
            $watcherId = $this->driver->onSignal($signal, static function () use ($listener) {
                $listener();
            });

            $this->signals[$signal][$watcherId] = $listener;
        } catch (Loop\UnsupportedFeatureException $e) {
            throw new \BadMethodCallException("Signals aren't available in the current environment.");
        }
    }

    /** @inheritdoc */
    public function removeSignal($signal, $listener)
    {
        if (!isset($this->signals[$signal])) {
            return;
        }

        $index = \array_search($listener, $this->signals[$signal], true);
        if ($index === false) {
            return;
        }

        $this->driver->cancel($index);

        unset($this->signals[$signal][$index]);
        if (empty($this->signals[$signal])) {
            unset($this->signals[$signal]);
        }
    }

    /** @inheritdoc */
    public function run()
    {
        $this->driver->run();
    }

    /** @inheritdoc */
    public function stop()
    {
        $this->driver->stop();
    }

    private function deferEnabling(string $watcherId)
    {
        $this->driver->disable($watcherId);
        $this->driver->defer(function () use ($watcherId) {
            try {
                $this->driver->enable($watcherId);
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
}
