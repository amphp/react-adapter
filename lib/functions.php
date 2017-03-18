<?php

namespace Amp\ReactAdapter;

use Amp\{ Loop, ReactAdapter };
use React\EventLoop\LoopInterface;

const ADAPTER_LOOP_IDENTIFIER = ReactAdapter::class;

function loop(): LoopInterface {
    $loop = Loop::getState(ADAPTER_LOOP_IDENTIFIER);
    if ($loop) {
        return $loop;
    }

    $loop = new ReactAdapter(Loop::get());
    Loop::setState(ADAPTER_LOOP_IDENTIFIER, $loop);
    return $loop;
}
