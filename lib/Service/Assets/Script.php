<?php

namespace Netdust\Service\Assets;

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
    public function getLocalization(): string
    {
        return $this->localized_var;
    }

    public function setLocalization( string $key, array $vars ): Script
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
     * Register asset
     * https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    protected function enqueueCallback(): callable
    {
        return function() {
            $this->localize();
            wp_enqueue_script(
                $this->getHandle(),
                $this->getUrl(),
                $this->getDependencies(),
                $this->getVersion(),
                $this->getInFooter());
        };
    }

    /**
     * Localize asset
     * https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     */
    protected function localize(): void {

        // If we actually have localized params, localize and enqueue.
        if ( ! $this->empty() ) {

            $localized = wp_localize_script( $this->getHandle(), $this->getLocalization(), $this->all() );

            if ( false === $localized ) {
                app()->make( LoggerInterface::class )->error(
                    'The script ' . $this->handle . ' failed to localize. That is all I know, unfortunately.',
                    'script_was_not_localized',
                    [ 'handle' => $this->handle ]
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
