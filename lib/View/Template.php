<?php

namespace Netdust\View;

use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders view templates using PHP.
 */
class Template implements TemplateInterface {

    /**
     * Group identifier for the template.
     *
     * @var string
     */
    protected string $group = '';

    /**
     * Root directories for template lookup.
     *
     * @var array
     */
    protected array $template_root = [];

    /**
     * Global data shared across all templates.
     *
     * @var array
     */
    protected array $globals;

    /**
     * Data specific to the current template.
     *
     * @var array
     */
    protected array $data;

    /**
     * The current depth level of nested template rendering.
     *
     * @var int
     */
    private int $depth = 0;

    /**
     * Constructor to initialize template root directories and global data.
     */
    public function __construct(string|array $template_root = '', array $globals = []) {
        $this->template_root = array_map('untrailingslashit', (array) $template_root);
        $this->globals = $globals;
    }

    /**
     * Adds a new directory to the list of template roots.
     */
    public function add(string $template_location): void {
        $this->template_root[] = $template_location;
    }

    /**
     * Outputs the rendered template directly.
     */
    public function print(string $template_name, array $data = []): void {
        echo $this->render($template_name, $data);
    }

    /**
     * Renders a template by merging global and specific data.
     */
    public function render(string $template_name, array $data = []): string {
        if ($this->exists($template_name)) {
            $template = $this->include_template($template_name, array_merge($this->globals, $data));
        } else {
            app()->make(LoggerInterface::class)->error(
                "Template $template_name was not loaded because the file located.",
                'template_file_does_not_exist'
            );
            $template = '';
        }

        return $template;
    }

    /**
     * Checks if a template file exists in the defined paths.
     */
    public function exists(string $template_name): bool {
        return file_exists($this->get_path($template_name));
    }

    /**
     * Gets the full path to a template file.
     */
    public function get_path(string $template_name): string {
        $found_template = locate_template(  preg_replace('/\.php$/', '', $template_name) . '.php');

        if (!$found_template) {
            foreach ($this->template_root as $folder) {
                $template = $folder . '/' . preg_replace('/\.php$/', '', $template_name) . '.php';
                if (file_exists($template)) {
                    $found_template = $template;
                    break;
                }
            }
        }

        return apply_filters("template:path", $found_template);
    }

    /**
     * Includes a template file while isolating the provided data.
     */
    private function include_template(string $template_name, array $data): bool|string {
        $this->depth++;
        $this->data[$this->depth] = apply_filters("template:params", $data);

        ob_start();

        do_action('template:before_template', [
            'template_name' => $template_name,
            'path' => $this->get_path($template_name),
        ]);

        $this->include_file_with_scope($this->get_path($template_name), [
            'template' => $this,
        ]);

        do_action('template:after_template', [
            'template_name' => $template_name,
            'path' => $this->get_path($template_name),
        ]);

        $result = ob_get_clean();

        unset($this->data[$this->depth]);
        $this->depth--;

        return $result;
    }

    /**
     * Retrieves a specific parameter from the current template's data.
     */
    public function get_param(string $param, mixed $default = false): mixed {
        return $this->data[$this->depth][$param] ?? $default;
    }

    /**
     * Retrieves all parameters for the current template.
     */
    public function get_params(): array {
        return $this->data[$this->depth] ?? [];
    }

    /**
     * Includes a PHP file with a scoped set of variables.
     */
    private function include_file_with_scope(string $file, array $scope): bool {
        if (file_exists($file)) {
            extract($scope);
            include $file;
            return true;
        }

        return false;
    }
}
