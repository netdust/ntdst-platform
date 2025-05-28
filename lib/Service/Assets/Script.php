<?php

namespace Netdust\Service\Assets;

use Netdust\Logger\Logger;
use Netdust\Logger\LoggerInterface;
use Netdust\Traits\Collection;

/**
 * Class Script
 */
class Script extends Asset
{

    use Collection;

    /**
     * @var bool
     */
    protected $inFooter = true;

    /**
     * @return bool
     */
    public function getInFooter(): bool
    {
        return $this->inFooter;
    }

    /**
     * @param bool $inFooter
     * @return $this
     */
    public function setInFooter(bool $inFooter): Script
    {
        $this->inFooter = $inFooter;
        return $this;
    }

    protected string $localized_var;

    /**
     * @return string
     */
    public function getLocalizedVar(): string
    {
        return $this->localized_var;
    }

    public function setLocalizedVar( string $key, array $vars = [] ): Script
    {
        $this->localized_var = $key;
        $this->collection = $vars;
        return $this;
    }

    /**
     * Returns true if the script has been enqueued. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_enqueued(): bool {
        return (bool) wp_scripts()->query( $this->getHandle(), 'enqueued' );
    }

    /**
     * Returns true if the script has been registered. Bypasses doing it wrong check.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_registered(): bool {
        return (bool) wp_scripts()->query( $this->getHandle(), 'registered' );
    }

    /**
     * enqueue asset
     * https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function enqueue(): callable
    {
        return function() {
            wp_enqueue_script(
                $this->getHandle(),
                $this->getUrl(),
                $this->getDependencies(),
                $this->getVersion(),
                $this->getInFooter());
            $this->localize();
        };
    }

    /**
     * Localize asset
     * https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    public function localize(): void {

        // If we actually have localized params, localize and enqueue.
        if ( ! $this->empty() ) {

            $localized = wp_localize_script( $this->getHandle(), $this->getLocalizedVar(), $this->all() );

            if ( false === $localized ) {
                throw new \Exception(
                    'The script ' . $this->handle . ' failed to localize. That is all I know, unfortunately.',
                );
            }
        }
    }


    /**
     * https://developer.wordpress.org/reference/hooks/script_loader_tag/
     * @return string
     */
    protected function getFilterLoaderTag(): string
    {
        return 'script_loader_tag';
    }
}
