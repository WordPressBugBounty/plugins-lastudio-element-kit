<?php

/**
 * Class: LaStudioKit_Woo_Single_Product_Content
 * Name: Product Content
 * Slug: lakit-wooproduct-content
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * Woo Widget
 */
class LaStudioKit_Woo_Single_Product_Content extends LaStudioKit_Post_Content {

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    public function get_style_depends()
    {
        return ['lastudio-kit-woocommerce' ];
    }

    public function show_in_panel() {
        return true;
    }

    public function get_name() {
        return 'lakit-wooproduct-content';
    }

    public function get_categories() {
        return [ 'lastudiokit-woo-product' ];
    }

    public function get_keywords() {
        return [ 'content', 'post', 'product' ];
    }

    public function get_widget_title() {
        return esc_html__( 'Product Content', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'eicon-post-content';
    }

    protected function register_controls() {
        parent::register_controls();
    }

}