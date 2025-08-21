<?php
/**
 * AssetManager class file.
 *
 */

namespace Netdust\Service\Assets;

use Netdust\Logger\Logger;
use Netdust\Traits\Collection;
use Netdust\Traits\Singleton;

/**
 * Asset Manager
 */
class AssetManager {

    use Singleton;
    use Collection;

    /**
     * Load a external script.
     */
    public function script( string $handle, string $src, array $params = [], $register = true ): Script {
        $this->add( $handle, new Script( $handle, $src ) );
        $this->get( $handle )
            ->setDependencies( $params['deps']??[] )
            ->setAttributes( $params['attr']??[] )
            ->setInFooter( $params['footer']??false )
            ->setVersion( $params['ver']??'0.1' )
            ->to( $params['to']??['front'] );

	    if( !empty( $local = $params['localized']??'' ) ) {
		    $this->get( $handle )->setLocalizedVar( $local[0], $local[1] );
	    }

        if( $register )
            $this->get( $handle )->register();


        return $this->get( $handle );
    }

    /**
     * Load an external stylesheet file.
     */
    public function style( string $handle, string $src, array $params = [], $register = true  ): Style {
        $style = ( new Style($handle, $src ) )
            ->setVersion( $params['ver']??'0.1' )
            ->setMedia( $params['media']??'')
            ->to( $params['to']??['front'] );

        if( $register )
             $style->register();

        return $style;
    }

}
