<?php

namespace Netdust\View;

interface TemplateInterface {
    public function print( string $template_name, array $data = [] ): void;
    public function render( string $template_name, array $data = [] ): string;
    public function exists( string $template_name ): bool;
}