<?php

/**
 * Class: LaStudioKit_Woo_Single_Product_AddToCart
 * Name: Product AddToCart
 * Slug: lakit-wooproduct-addtocart
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * Woo Widget
 */
class LaStudioKit_Woo_Single_Product_AddToCart extends LaStudioKit_Base
{

    protected function enqueue_addon_resources()
    {
        if (!lastudio_kit_settings()->is_combine_js_css()) {
            $this->add_style_depends('lastudio-kit-woocommerce');
            $this->add_script_depends('lastudio-kit-base');
        }
        wp_register_script(  'lakit-buynow' , lastudio_kit()->plugin_url('assets/js/addons/buynow.min.js') , [],  lastudio_kit()->get_version() , true );
        $this->add_script_depends('lakit-buynow');
    }

    public function get_name()
    {
        return 'lakit-wooproduct-addtocart';
    }

    public function get_categories()
    {
        return ['lastudiokit-woo-product'];
    }

    public function get_keywords()
    {
        return ['woocommerce', 'shop', 'store', 'cart', 'product', 'button', 'add to cart'];
    }

    public function get_widget_title()
    {
        return esc_html__('Add To Cart', 'lastudio-kit');
    }

    public function get_icon()
    {
        return 'eicon-product-add-to-cart';
    }

    protected function render()
    {
        $_product_id = $this->get_settings_for_display('product_id');
        $product_id = !empty($_product_id) ? $_product_id : false;

        global $product;
        $product = wc_get_product($product_id);

        if (empty($product)) {
            return;
        }

        $this->add_render_attribute('_wrapper', 'data-product_id', $product->get_id());

        add_action('woocommerce_after_add_to_cart_button', [ $this, 'action_add_more_buttons' ], 50);

//        if( !$product->is_purchasable()){
//            return;
//        }
        ?>

        <div class="elementor-add-to-cart elementor-product-<?php echo esc_attr($product->get_type()); ?>">
            <?php
            woocommerce_template_single_add_to_cart();
            ?>
        </div>

        <?php

        remove_action('woocommerce_after_add_to_cart_button', [ $this, 'action_add_more_buttons' ], 50);

        // On render widget from Editor - trigger the init manually.
        if (wp_doing_ajax()) {
            ?>
            <script>
                jQuery(document).trigger('lastudiokit/woocommerce/single/add-to-cart lastudio-kit/woocommerce/single/add-to-cart');
            </script>
            <?php
        }
    }

    public function action_add_more_buttons()
    {
        global $product;
        if( $product instanceof \WC_Product) {
            $extra_buttons = $this->render_extra_buttons($product);
            if( !empty($extra_buttons['wishlist']) || !empty($extra_buttons['compare']) ) {
                $_html = '';
                if(!empty($extra_buttons['wishlist'])) {
                    $_html .= $extra_buttons['wishlist'];
                }
                if(!empty($extra_buttons['compare'])) {
                    $_html .= $extra_buttons['compare'];
                }
                echo sprintf('<div class="lakit-wrap-core-wl-cp">%1$s</div>', $_html);
            }
            if(!empty($extra_buttons['buynow'])){
                echo sprintf('<div class="lakit-wrap-buynow">%1$s</div>', $extra_buttons['buynow']);
            }
        }
    }

    protected function render_extra_buttons( $product ){

        $btn_output = [];
        $product_url    = $product->get_permalink();
        $btn_format     = '<a data-product_id="%1$s" class="%2$s" href="%3$s">%4$s</a>';

        /**
         * Wishlist
         */
        $wl_icon            = $this->_get_icon_setting($this->get_settings_for_display('wl_icon'));
        $wl_normal_text     = $this->get_settings_for_display('wl_text');
        if(!empty($wl_icon) || !empty($wl_normal_text)){
            $wl_classes = ['add_wishlist', 'la-core-wishlist', 'button', 'e-btn'];
            $wl_html    = '';
            if(!empty($wl_icon)){
                $wl_html .= sprintf('<span class="lakit-btn--icon">%s</span>', $wl_icon);
            }
            if(!empty($wl_normal_text)){
                $wl_html .= sprintf('<span class="lakit-btn--text">%s</span>', esc_html($wl_normal_text));
            }
            $btn_output['wishlist'] = sprintf(
                $btn_format,
                $product->get_id(),
                esc_attr(join(' ', $wl_classes)),
                esc_url($product_url),
                $wl_html
            );
        }
        /**
         * Compare
         */
        $cp_icon            = $this->_get_icon_setting($this->get_settings_for_display('cp_icon'));
        $cp_normal_text     = $this->get_settings_for_display('cp_text');
        if(!empty($cp_icon) || !empty($cp_normal_text)){
            $cp_classes = ['add_compare', 'la-core-compare', 'button', 'e-btn'];
            $cp_html    = '';
            if(!empty($cp_icon)){
                $cp_html .= sprintf('<span class="lakit-btn--icon">%1$s</span>', $cp_icon);
            }
            if(!empty($cp_normal_text)){
                $cp_html .= sprintf('<span class="lakit-btn--text">%1$s</span>', esc_html($cp_normal_text));
            }
            $btn_output['compare'] = sprintf(
                $btn_format,
                $product->get_id(),
                esc_attr(join(' ', $cp_classes)),
                esc_url($product_url),
                $cp_html
            );
        }
        /**
         * Buy now
         */
        $product_url       = $product->add_to_cart_url();
        $checkout_url       = wc_get_checkout_url();
        if($product->is_type('simple')){
            $product_url = add_query_arg([
                'add-to-cart' => $product->get_id(),
                'lakit_buynow' => 'yes'
            ], $checkout_url);
        }
        $bn_icon            = $this->_get_icon_setting($this->get_settings_for_display('bn_icon'));
        $bn_normal_text     = $this->get_settings_for_display('bn_text');
        $btn_format     = '<a data-product_id="%1$s" class="%2$s" href="%3$s" data-type="%5$s" data-checkout="%6$s">%4$s</a>';
        if(!empty($bn_icon) || !empty($bn_normal_text)){
            $bn_classes = ['lakit-core-buynow', 'button', 'e-btn'];
            $bn_html    = '';
            if(!empty($bn_icon)){
                $bn_html .= sprintf('<span class="lakit-btn--icon">%1$s</span>', $bn_icon);
            }
            if(!empty($bn_normal_text)){
                $bn_html .= sprintf('<span class="lakit-btn--text">%1$s</span>', esc_html($bn_normal_text));
            }
            $btn_output['buynow'] = sprintf(
                $btn_format,
                $product->get_id(),
                esc_attr(join(' ', $bn_classes)),
                esc_url($product_url),
                $bn_html,
                $product->get_type(),
                esc_url($checkout_url)
            );
        }
        return $btn_output;
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'section_product',
            [
                'label' => esc_html__('Product', 'lastudio-kit'),
            ]
        );
        $this->add_control(
            'wc_product_warning',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Leave a blank to get the data for current product.', 'lastudio-kit'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        $this->add_control(
            'product_id',
            [
                'label' => esc_html__('Product', 'lastudio-kit'),
                'type' => 'lastudiokit-query',
                'options' => [],
                'label_block' => true,
                'autocomplete' => [
                    'object' => 'post',
                    'query' => [
                        'post_type' => ['product'],
                    ],
                ],
            ]
        );

        $this->_add_control(
            'show_wishlist',
            array(
                'label'        => esc_html__( 'Show Wishlist', 'lastudio-kit' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'true',
                'default'      => '',
            )
        );

        $this->_add_control(
            'show_compare',
            array(
                'label'        => esc_html__( 'Show Compare', 'lastudio-kit' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'true',
                'default'      => '',
            )
        );

        $this->_add_control(
            'show_buynow',
            array(
                'label'        => esc_html__( 'Show Buy Now', 'lastudio-kit' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'true',
                'default'      => '',
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_atc_button_style',
            [
                'label' => esc_html__('Button', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'wc_style_warning',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'lastudio-kit'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->_add_responsive_control(
            'button_width',
            [
                'label' => esc_html__( 'Button Width', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .button.alt' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => esc_html__('Alignment', 'lastudio-kit'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'prefix_class' => 'elementor-add-to-cart%s--align-',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .cart button:not(.qty-btn)',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .cart button:not(.qty-btn)',
                'exclude' => ['color'],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Padding', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label' => esc_html__('Margin', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('button_style_tabs');

        $this->start_controls_tab('button_style_normal',
            [
                'label' => esc_html__('Normal', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_border_color',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('button_style_hover',
            [
                'label' => esc_html__('Hover', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn):hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn):hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn):hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_transition',
            [
                'label' => esc_html__('Transition Duration', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.2,
                ],
                'range' => [
                    'px' => [
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart button:not(.qty-btn)' => 'transition: all {{SIZE}}s',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_atc_quantity_style',
            [
                'label' => esc_html__('Quantity', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'spacing',
            [
                'label' => esc_html__('Spacing', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'custom'],
                'selectors' => [
                    'body:not(.rtl) {{WRAPPER}} .quantity + .button' => 'margin-left: {{SIZE}}{{UNIT}}',
                    'body.rtl {{WRAPPER}} .quantity + .button' => 'margin-right: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'quantity_typography',
                'selector' => '{{WRAPPER}} .quantity .qty',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'quantity_border',
                'selector' => '{{WRAPPER}} .quantity .qty',
                'exclude' => ['color'],
            ]
        );

        $this->add_control(
            'quantity_border_radius',
            [
                'label' => esc_html__('Border Radius', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .wrap-cart-cta .quantity' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_padding',
            [
                'label' => esc_html__('Padding', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .wrap-cart-cta .quantity' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('quantity_style_tabs');

        $this->start_controls_tab('quantity_style_normal',
            [
                'label' => esc_html__('Normal', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            'quantity_text_color',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .quantity' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quantity_bg_color',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .wrap-cart-cta .quantity' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quantity_border_color',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .wrap-cart-cta .quantity' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('quantity_style_focus',
            [
                'label' => esc_html__('Focus', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            'quantity_text_color_focus',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty:focus' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .wrap-cart-cta .quantity:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quantity_bg_color_focus',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty:focus' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .wrap-cart-cta .quantity:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quantity_border_color_focus',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty:focus' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .wrap-cart-cta .quantity:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quantity_transition',
            [
                'label' => esc_html__('Transition Duration', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.2,
                ],
                'range' => [
                    'px' => [
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .quantity .qty' => 'transition: all {{SIZE}}s',
                    '{{WRAPPER}} .wrap-cart-cta .quantity' => 'transition: all {{SIZE}}s',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_atc_variations_style',
            [
                'label' => esc_html__('Variations', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'variations_width',
            [
                'label' => esc_html__('Width', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%', 'px', 'em', 'custom'],
                'default' => [
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{WRAPPER}} form.cart .variations' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'variations_spacing',
            [
                'label' => esc_html__('Spacing', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} form.cart .variations' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'heading_variations_text_style',
            [
                'label' => esc_html__('Text', 'lastudio-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'variations_text_typography',
                'selector' => '{{WRAPPER}} form.cart .woocommerce-variation',
            ]
        );
        $this->add_control(
            'variations_text_color',
            [
                'label' => esc_html__('Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} form.cart .woocommerce-variation' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'heading_variations_label_style',
            [
                'label' => esc_html__('Label', 'lastudio-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'variations_label_color_focus',
            [
                'label' => esc_html__('Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} form.cart table.variations label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'variations_label_typography',
                'selector' => '{{WRAPPER}} form.cart table.variations label',
            ]
        );

        $this->add_control(
            'heading_variations_select_style',
            [
                'label' => esc_html__('Select field', 'lastudio-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'variations_select_color',
            [
                'label' => esc_html__('Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} form.cart table.variations td.value select' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'variations_select_bg_color',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} form.cart table.variations td.value:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'variations_select_border_color',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} form.cart table.variations td.value:before' => 'border: 1px solid {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'variations_select_typography',
                'selector' => '{{WRAPPER}} form.cart table.variations td.value select, div.product.elementor{{WRAPPER}} form.cart table.variations td.value:before',
            ]
        );

        $this->add_control(
            'variations_select_border_radius',
            [
                'label' => esc_html__('Border Radius', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} form.cart table.variations td.value:before' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->register_wishlist_settings();

        $this->register_compare_settings();

        $this->register_buynow_settings();
    }

    public function render_plain_content()
    {
    }

    public function register_wishlist_settings()
    {
        $this->start_controls_section(
            'section_wishlist_setting',
            [
                'label' => esc_html__('Wishlist Settings', 'lastudio-kit'),
                'condition' => [
                    'show_wishlist' => 'true',
                ]
            ]
        );
        $this->_add_control(
            'wl_text',
            [
                'label' => esc_html__( 'Text', 'lastudio-kit' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Add to wishlist', 'lastudio-kit' ),
                'placeholder' => esc_html__( 'Add to wishlist', 'lastudio-kit' ),
            ]
        );
        $this->add_control(
            'wl_icon',
            [
                'label'            => esc_html__( 'Icon', 'lastudio-kit' ),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin'             => 'inline',
                'label_block'      => false,
                'default' => array(
                    'value'     => 'lastudioicon-heart-2',
                    'library'   => 'lastudioicon'
                )
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_wishlist_style',
            [
                'label' => esc_html__('Wishlist Button', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_wishlist' => 'true',
                ]
            ]
        );

        $this->register_button_styles('wl', '.add_wishlist');

        $this->end_controls_section();
    }

    public function register_compare_settings()
    {
        $this->start_controls_section(
            'section_compare_setting',
            [
                'label' => esc_html__('Compare Settings', 'lastudio-kit'),
                'condition' => [
                    'show_compare' => 'true',
                ]
            ]
        );
        $this->_add_control(
            'cp_text',
            [
                'label' => esc_html__( 'Text', 'lastudio-kit' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Add to compare', 'lastudio-kit' ),
                'placeholder' => esc_html__( 'Add to compare', 'lastudio-kit' ),
            ]
        );
        $this->add_control(
            'cp_icon',
            [
                'label'            => esc_html__( 'Icon', 'lastudio-kit' ),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin'             => 'inline',
                'label_block'      => false,
                'default' => array(
                    'value'     => 'lastudioicon-ic_compare_arrows_24px',
                    'library'   => 'lastudioicon'
                )
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_compare_style',
            [
                'label' => esc_html__('Compare Button', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_compare' => 'true',
                ]
            ]
        );

        $this->register_button_styles('cp', '.add_compare');

        $this->end_controls_section();
    }

    public function register_buynow_settings()
    {
        $this->start_controls_section(
            'section_buynow_setting',
            [
                'label' => esc_html__('BuyNow Settings', 'lastudio-kit'),
                'condition' => [
                    'show_buynow' => 'true',
                ]
            ]
        );
        $this->_add_control(
            'bn_text',
            [
                'label' => esc_html__( 'Text', 'lastudio-kit' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Buy Now', 'lastudio-kit' ),
                'placeholder' => esc_html__( 'Buy Now', 'lastudio-kit' ),
            ]
        );
        $this->add_control(
            'bn_icon',
            [
                'label'            => esc_html__( 'Icon', 'lastudio-kit' ),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin'             => 'inline',
                'label_block'      => false,
                'default' => array(
                    'value'     => 'lastudioicon-cart-add-2',
                    'library'   => 'lastudioicon'
                )
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_buynow_style',
            [
                'label' => esc_html__('BuyNow Button', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_buynow' => 'true',
                ]
            ]
        );
        $this->_add_responsive_control(
             'bn_button_width',
            [
                'label' => esc_html__( 'Button Width', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-wrap-buynow' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ppc-button-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->register_button_styles('bn', '.lakit-core-buynow');

        $this->end_controls_section();
    }

    public function register_button_styles( $key, $btnClass ){
        $this->add_responsive_control(
            $key .'_alignment',
            [
                'label' => esc_html__('Alignment', 'lastudio-kit'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'lastudio-kit'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'selectors_dictionary' => [
                    'left' => '--e-align: left;',
                    'center' => '--e-align: center;',
                    'right' => '--e-align: right;',
                    'justify' => '--e-align: justify;',
                ],
                'selectors' => [
                    '{{WRAPPER}} '.  $btnClass => '{{VALUE}}',
                ],
            ]
        );

        $this->_add_responsive_control(
            $key . '_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} '.  $btnClass => '--e-iconsize: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->_add_responsive_control(
            $key .'_icon_indent',
            [
                'label' => esc_html__( 'Icon Spacing', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} '.  $btnClass => '--e-icongap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->_add_responsive_control(
            $key . '_icon_vert_spacing',
            [
                'label' => esc_html__( 'Vertical Icon Spacing', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range' => [
                    'px' => array(
                        'min' => -30,
                        'max' => 30,
                    ),
                ],
                'selectors' => [
                    '{{WRAPPER}} '.  $btnClass => '--e-iconvspace: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $key .'_typography',
                'selector' => '{{WRAPPER}} '. $btnClass,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $key .'_border',
                'selector' => '{{WRAPPER}} '. $btnClass,
                'exclude' => ['color'],
            ]
        );

        $this->add_control(
            $key .'_radius',
            [
                'label' => esc_html__('Border Radius', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $key .'_padding',
            [
                'label' => esc_html__('Padding', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            $key .'_margin',
            [
                'label' => esc_html__('Margin', 'lastudio-kit'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs($key .'_button_style_tabs');

        $this->start_controls_tab($key .'_button_style_normal',
            [
                'label' => esc_html__('Normal', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            $key .'_text_color',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            $key .'_bg_color',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            $key .'_border_color',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab($key .'_button_style_hover',
            [
                'label' => esc_html__('Hover', 'lastudio-kit'),
            ]
        );

        $this->add_control(
            $key .'_text_color_hover',
            [
                'label' => esc_html__('Text Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass . ':hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            $key .'_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass . ':hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            $key .'_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'lastudio-kit'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass . ':hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            $key .'_transition',
            [
                'label' => esc_html__('Transition Duration', 'lastudio-kit'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.2,
                ],
                'range' => [
                    'px' => [
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} '. $btnClass => 'transition: all {{SIZE}}s',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
    }
}