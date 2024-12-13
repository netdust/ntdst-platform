<?php

namespace Netdust\Service\Assets;


/**
 * Class Asset
 */
abstract class Asset
{

    const LOCATIONS_BY_HOOK = [
        'admin' => 'admin_enqueue_scripts',
        'login' => 'login_enqueue_scripts',
        'front' => 'wp_enqueue_scripts',
    ];

    protected string $handle;

    protected string $file;

    protected string|bool|array $dependencies = [];

    protected bool|string|null $version = '0.1';

    protected array $attributes = [];

    protected array $inline = [];


    protected array $localisations;

    /**
     * Asset constructor.
     * @param string $handle
     * @param string $src
     */
    public function __construct(string $handle, string $src)
    {
        $this->handle = $handle;
        $this->file = $src;
        $this->localisations[] = 'front';
    }


    public function getHandle(): string
    {
        return $this->handle;
    }


    public function setHandle(string $handle): Asset
    {
        $this->handle = $handle;
        return $this;
    }

    public function getDependencies(): string|bool|array
    {
        return $this->dependencies;
    }

    public function setDependencies(string|bool|array $dependencies): Asset
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function getVersion(): bool|string|null
    {
        return $this->version;
    }

    public function setVersion($version): Asset
    {
        $this->version = $version;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->file;
    }


    public function setAttributes(array $attributes): Asset
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function to( string|array $locations): Asset
    {
        $this->localisations = (array)$locations;
        return $this;
    }

    public function register(): Asset
    {
        foreach ($this->localisations as $localisation) {
            $hook = self::LOCATIONS_BY_HOOK[$localisation] ?? null;
            if($hook) {
                add_action($hook, $this->enqueueCallback());
            }
        }

        $this->registerAttributes();
        return $this;
    }

    protected function registerAttributes(): void
    {
        add_filter($this->getFilterLoaderTag(), function(string $tag, ?string $handle) {

            if($this->getHandle() !== $handle) {
                return $tag;
            }

            return preg_replace(
                '/(src|href)(.+>)/',
                $this->buildAttributes($this->attributes).' $1$2',
                $tag);
        }, 10, 2);
    }

    protected function buildAttributes( array $attributes  ): string {
        return join(' ', array_map(function($key) use ($attributes) {
            return is_bool($attributes[$key]) ? ($attributes[$key]?$key:'') : ($key.'="'.$attributes[$key].'"');
        }, array_keys($attributes)));
    }

    /**
     * Register asset with wp_enqueue_script or wp_enqueue_style
     * @return callable
     */
    protected abstract function enqueueCallback(): callable;

    /**
     * Returns the filter name
     *
     * @return string
     */
    protected abstract function getFilterLoaderTag(): string;

}