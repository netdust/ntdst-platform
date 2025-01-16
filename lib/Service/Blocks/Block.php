<?php

namespace Netdust\Service\Blocks;

use Netdust\Logger\Logger;
use Netdust\Service\Assets\Script;


/**
 * Class AbstractBlock
 */
abstract class Block
{


    /**
     * @var string
     */
    protected string $namespace = 'ntdst';

    /**
     * api_version parameter for register_block_type function
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     * @var int
     */
    protected int $apiVersion = 1;

    /**
     * @var string
     */
    protected string $blockName;

    /**
     * Attributes of the block when rendering
     * @var array
     */
    protected array $attributes = [];

    /**
     * Block content when rendering
     * @var string|null
     */
    protected ?string $content;

    public function __construct( string $block_name, $args = array() )
    {
        $this->blockName = $block_name;
        $this->attributes = $args;
        $this->initialize();
    }

    /**
     * @return void
     */
    protected function initialize(): void
    {
        if (empty($this->blockName)) {
            _doing_it_wrong(__METHOD__, 'Block name is required.', '5.0.0');
            return;
        }

        $this->registerBlocksAssets();
        $this->registerBlock();
    }

    /**
     * Register script and style assets for the block type before it is registered.
     * @return void
     */
    protected function registerBlocksAssets(): void
    {
        $scriptEditor = $this->getBlockEditorScript();
        if ($scriptEditor) {
            (new Script($scriptEditor['handle'], $scriptEditor['path']))
                ->setDependencies($scriptEditor['dependencies'])
                ->to('admin')
                ->register();
        }
    }

    /**
     * @return string
     */
    protected function getBlockType(): string
    {
        return $this->namespace . '/' . $this->blockName;
    }

    /**
     * @return array
     */
    protected function getBlockAttributes(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getBlockSupports(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getBlockRenderCallback(): array
    {
        return [$this, 'renderCallback'];
    }

    /**
     * @param array $attributes
     * @param string|null $content
     * @return string|null
     */
    abstract protected function renderBlock(array $attributes = [], ?string $content = null): ?string;

    /**
     * @param array $attributes
     * @param string|null $content
     * @return string|null
     */
    public function renderCallback(array $attributes = [], ?string $content = null): ?string
    {
        $this->attributes = $attributes;
        $this->content = $content;
        return $this->renderBlock($attributes, $content);
    }

    /**
     * @return void
     */
    protected function registerBlock(): void
    {
        add_action('init', function () {
            $block_type = $this->getBlockType();

            if( file_exists( $block_type ) ) {
                register_block_type(
                    $block_type,
                    array(
                        'render_callback' => $this->getBlockRenderCallback(),
                    )
                );
            }
            else {
                register_block_type($block_type, [
                    'render_callback' => $this->getBlockRenderCallback(),
                    'editor_script' => $this->getBlockEditorScript('handle'),
                    'editor_style' => $this->getBlockEditorStyleHandle(),
                    'style' => $this->getBlockFrontendStyleHandle(),
                    'attributes' => $this->getBlockAttributes(),
                    'supports' => $this->getBlockSupports(),
                    'api_version' => $this->apiVersion,
                ] );
            }

        });
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    protected function getBlockEditorScript(string $key = '')
    {

        $basePath = get_stylesheet_directory_uri().'/static/blocks/';

        $script = [
            'handle' => $this->namespace . '-' . $this->blockName . '-block',
            'path' => $basePath . $this->blockName . '.js',
            'dependencies' => $this->getBlockEditorScriptDependencies(),
        ];

        return $script[$key] ?? $script;
    }

    /**
     * @return string[]
     */
    protected function getBlockEditorScriptDependencies(): array
    {
        return ['wp-blocks', 'lodash', 'wp-block-editor', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components'];
    }

    /**
     * @return string
     */
    public function getBlockEditorStyleHandle(): string
    {
        return $this->namespace . '-' . 'blocks-editor-style';
    }

    /**
     * @return string
     */
    protected function getBlockFrontendStyleHandle(): string
    {
        return $this->namespace . '-' . 'style';
    }
}