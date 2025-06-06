<?php

/**
 * Admin Page
 *
 * @since   1.0.0
 */


namespace Netdust\Service\Pages;

use Netdust\App;
use Netdust\Http\Request;
use Netdust\Logger\Logger;
use Netdust\Traits\Templates;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use stdClass;
use WP_Post;


class VirtualPage
{
    protected $wpPost;

    protected string $uri;
    protected string $title;
    protected ?string $template;


    public function template(): ?string {
        return $this->template;
    }

    public function title(): string {
        return $this->title;
    }

    public function uri(): string {
        return $this->uri;
    }

    public function __construct(string $uri, string $title, string $template = null )
    {
        $this->uri = $uri;
        $this->title = $title;
        $this->template = $template;

        if( !empty($this->template) ) {
            add_filter('page_template', [&$this, 'loadTemplate'] );
            add_filter('theme_page_templates', function($templates) {
                $templates[$this->template] =  $this->title . ' Template';
                return $templates;
            });
        }
    }

    public function onRoute(): void
    {

        $this->rewrite();
        $this->createPage();

        //set 200 header
        @status_header( 200 );
        nocache_headers();

    }


    function rewrite() {
        add_rewrite_endpoint( $this->template, EP_PERMALINK | EP_PAGES );

        if(get_transient( 'ntdst_vp_flush' )) {
            delete_transient( 'ntdst_vp_flush' );
            flush_rewrite_rules();
        }
    }

    public function loadTemplate( string $template ): string {
        remove_filter( 'page_template', [$this, 'loadTemplate']);

        if(  $_SERVER['REQUEST_URI'] == '/'.$this->uri ) {
            return $this->template;
        }

        return $template;
    }

    private function createPostInstance( ): WP_Post {

        if (!isset($this->wpPost) )
        {
            $post = new stdClass();
            $post->ID = 0;
            $post->ancestors = array(); // 3.6
            $post->page_template = 'default'; // 3.6
            $post->comment_status = 'closed';
            $post->comment_count = 0;
            $post->filter = 'raw';
            $post->guid = home_url($this->uri);
            $post->is_virtual = true;
            $post->menu_order = 0;
            $post->pinged = '';
            $post->ping_status = 'closed';
            $post->post_title = $this->title;
            $post->post_name = $this->uri;
            $post->post_excerpt = '';
            $post->post_parent = 0;
            $post->post_type = 'page';
            $post->post_status = 'publish';
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql', 1);
            $post->modified = $post->post_date;
            $post->modified_gmt = $post->post_date_gmt;
            $post->post_password = '';
            $post->post_content_filtered = '';
            $post->post_author = is_user_logged_in() ? get_current_user_id() : 0;
            $post->post_content = '';
            $post->post_mime_type = '';
            $post->to_ping = '';

            $this->wpPost = new \WP_Post($post);
        }



        return ( $this->wpPost );
    }

    public function createPage(): void {

        remove_action('wp_loaded', [$this, 'createPage']);
        $this->createPostInstance();

        global $wp, $wp_query;

        // Update the main query
        $wp_query->current_post = $this->wpPost->ID;
        $wp_query->found_posts = 1;
        $wp_query->is_page = true;//important part
        $wp_query->is_singular = true;//important part
        $wp_query->is_single = false;
        $wp_query->is_attachment = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        $wp_query->is_tag = false;
        $wp_query->is_tax = false;
        $wp_query->is_author = false;
        $wp_query->is_date = false;
        $wp_query->is_year = false;
        $wp_query->is_month = false;
        $wp_query->is_day = false;
        $wp_query->is_time = false;
        $wp_query->is_search = false;
        $wp_query->is_feed = false;
        $wp_query->is_comment_feed = false;
        $wp_query->is_trackback = false;
        $wp_query->is_home = false;
        $wp_query->is_embed = false;
        $wp_query->is_404 = false;
        $wp_query->is_paged = false;
        $wp_query->is_admin = false;
        $wp_query->is_preview = false;
        $wp_query->is_robots = false;
        $wp_query->is_posts_page = false;
        $wp_query->is_post_type_archive = false;
        $wp_query->max_num_pages = 1;
        $wp_query->post = $this->wpPost;
        $wp_query->posts = array($this->wpPost);
        $wp_query->post_count = 1;
        $wp_query->queried_object = $this->wpPost;
        $wp_query->queried_object_id = $this->wpPost->ID;
        $wp_query->query_vars['error'] = '';
        unset($wp_query->query['error']);

        $GLOBALS['wp_query'] = $wp_query;

        $wp->query = array();
        $wp->register_globals();
        wp_cache_add(0, $this->wpPost, 'posts');
    }

}