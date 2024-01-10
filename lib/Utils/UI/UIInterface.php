<?php

namespace Netdust\Utils\UI;

interface UIInterface {

    public function make(string $type, $value, array $params = [],  $echo = false );

}