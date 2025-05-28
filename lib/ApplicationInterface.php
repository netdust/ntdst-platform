<?php

namespace Netdust;
interface ApplicationInterface {

    public function boot( ): void;
    public function register( ): void;
    public function get( string $id ): mixed;

}