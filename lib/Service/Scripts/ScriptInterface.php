<?php

namespace Netdust\Service\Scripts;

interface ScriptInterface {
    public function do_actions();
    public function enqueue();
}
