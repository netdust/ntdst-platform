<?php

namespace Netdust;
interface ApplicationInterface {

    public function boot( ): void;
    public function register( ): void;
    public function get( string $id ): mixed;
    public function make( string $id, mixed $implementation = null, array $args = null, array $afterBuildMethods = null ): mixed;

}