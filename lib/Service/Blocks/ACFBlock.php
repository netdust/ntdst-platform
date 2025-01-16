<?php
/**
 * Registers a shortcode
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Netdust\Service\Blocks;

use Netdust\App;
use Netdust\Logger\Logger;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Block
 *
 */
class ACFBlock extends Block {


    protected array $json;

    protected function getBlockPath(): string
    {
        return 'app/blocks/' . $this->blockName;
    }

    protected function getBlockType(): string
    {
        return App::file()->file_path( $this->getBlockPath() .'/block.json' );
    }

    protected function getBlockAttributes(): array
    {
        return (array) $this->json['attributes'];
    }

    protected function initialize(): void
    {
        $this->json = wp_json_file_decode( $this->getBlockType(), array( 'associative' => true ) );

        if ( ! $this->json ) {
            return;
        }

        parent::initialize();

    }

    protected function renderBlock(array $attributes = [], ?string $content = null): ?string
    {

        return App::template()->print( $this->getBlockPath() . '/' . $this->json['acf']['renderTemplate']??'block', array_merge($attributes, ['content'=>$content]) );

    }


    public function as_shortcode( array $atts ): string {
        ob_start();
        echo $this->renderBlock( $atts, '' );
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function __get( string $key ): mixed {
        if ( isset( $this->$key ) ) {
            return $this->$key;
        } else {
            return new \WP_Error( 'acfblock_param_not_set', 'The key ' . $key . ' could not be found.' );
        }
    }

}