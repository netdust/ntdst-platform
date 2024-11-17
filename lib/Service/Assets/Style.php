<?php

namespace Netdust\Service\Assets;

/**
 * Class Script
 */
class Style extends Asset
{
    /**
     * @var null|string
     */
    protected ?string $media = null;

    /**
     * @return string|null
     */
    public function getMedia(): ?string
    {
        return $this->media;
    }

    /**
     * @param string $media
     * @return $this
     */
    public function setMedia(string $media): self
    {
        $this->media = $media;
        return $this;
    }

    /**
     * Register asset
     * https://developer.wordpress.org/reference/functions/wp_enqueue_style/
     */
    protected function enqueueCallback(): callable
    {
        return function () {
            wp_enqueue_style(
                $this->getHandle(),
                $this->getUrl(),
                $this->getDependencies(),
                $this->getVersion(),
                $this->getMedia());
        };
    }

    /**
     * https://developer.wordpress.org/reference/hooks/style_loader_tag/
     * @return string
     */
    protected function getFilterLoaderTag(): string
    {
        return 'style_loader_tag';
    }
}
