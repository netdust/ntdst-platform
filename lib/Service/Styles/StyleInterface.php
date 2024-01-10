<?php

namespace Netdust\Service\Styles;

interface StyleInterface {
    public function do_actions(): void;
    public function enqueue(): void;
}
