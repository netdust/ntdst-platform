<?php

namespace Netdust\Service\Scripts;

interface ScriptInterface {

    public function do_actions(): void;
    public function enqueue(): void;
}
