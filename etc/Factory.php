<?php

namespace React\EventLoop;

use Amp\ReactAdapter\ReactAdapter;

/**
 * Class used to overwrite React's loop factory with an implementation returning the adaptor.
 *
 * @noinspection PhpUndefinedClassInspection
 */
final class Factory
{
    const SILENCE_CONST_NAME = 'AMP_SILENCE_REACT_LOOP_FACTORY_NOTICE';

    private static $noticeIssued = false;

    public static function create(): LoopInterface
    {
        if (!self::$noticeIssued) {
            self::$noticeIssued = true;

            $env = \getenv(self::SILENCE_CONST_NAME) ?: '0';
            $env = ($env !== '0' && $env !== 'false');
            $const = \defined(self::SILENCE_CONST_NAME) && \constant(self::SILENCE_CONST_NAME);
            if (!$const && !$env) {
                \trigger_error(__METHOD__ . "() overridden to return Amp's adapted event loop; "
                    . "this notice may be silenced by defining a constant or environment variable named "
                    . self::SILENCE_CONST_NAME, E_USER_NOTICE);
            }
        }

        return ReactAdapter::get();
    }
}
