<?php
/**
 * AJAX request abstract class file.
 *
 * @since 4.8.0
 *
 * @package LearnDash\Core
 */

namespace Netdust\Http;


/**
 * AJAX request handler abstract class.
 *
 * @since 4.8.0
 */
abstract class AjaxRequestHandler {

    /**
     * Action hook used by the AJAX class.
     *
     * @var string
     */
    const ACTION = 'my_plugin';

    /**
     * Action argument used by the nonce validating the AJAX request.
     *
     * @var string
     */
    const NONCE = 'my-plugin-ajax';


    public function do_actions(): void
    {
        add_action('wp_ajax_' . self::ACTION, [$this, 'handle']);
        add_action('wp_ajax_nopriv_' . self::ACTION, [$this, 'handle']);
        add_action('wp_loaded', [$this, 'register']);
    }

    public function handle()
    {
        // Make sure we are getting a valid AJAX request
        check_ajax_referer(self::NONCE);

        // Stand back! I'm about to try... SCIENCE!

        die();
    }


}
