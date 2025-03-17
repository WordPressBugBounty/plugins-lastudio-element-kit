<?php
namespace LaStudioKitThemeBuilder\Modules\NestedElements\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use LaStudioKitExtensions\Elementor\Controls\Group_Control_Box_Shadow;
use LaStudioKitThemeBuilder\Modules\NestedElements\Base\Widget_Nested_Base;
use LaStudioKitThemeBuilder\Modules\NestedElements\Controls\Control_Nested_Repeater;

class Nested_Carousel extends Widget_Nested_Base {

    protected function enqueue_addon_resources() {
        if ( ! lastudio_kit_settings()->is_combine_js_css() ) {
            $this->add_script_depends( 'lastudio-kit-w__ncarousel' );
            if ( !lastudio_kit()->is_optimized_css_mode() ) {
                $this->add_style_depends( 'lastudio-kit-swiper-dotv2' );
            }
        }
    }

    public function get_widget_css_config( $widget_name ) {
        $file_url  = lastudio_kit()->plugin_url( 'assets/css/addons/n-carousel.min.css' );
        $file_path = lastudio_kit()->plugin_path( 'assets/css/addons/n-carousel.min.css' );

        return [
            'key'       => 'lastudio-kit-swiper-dotv2',
            'version'   => lastudio_kit()->get_version( true ),
            'file_path' => $file_path,
            'data'      => [
                'file_url' => $file_url
            ]
        ];
    }

    public function get_style_depends() {
        return [ 'e-swiper', 'lastudio-kit-swiper-dotv2' ];
    }

	public function get_name() {
		return 'lakit-nested-carousel';
	}

	public function get_widget_title() {
		return esc_html__( 'Nested Carousel', 'lastudio-kit' );
	}

	public function get_icon() {
		return 'eicon-nested-carousel';
	}

	public function get_keywords() {
		return [ 'Carousel', 'Slides', 'Nested', 'Media', 'Gallery', 'Image' ];
	}

    protected function get_default_children_elements() {
        return [
            [
                'elType' => 'container',
                'settings' => [
                    '_title' => __( 'Slide #1', 'lastudio-kit' ),
                    'content_width' => 'full',
                ],
            ],
            [
                'elType' => 'container',
                'settings' => [
                    '_title' => __( 'Slide #2', 'lastudio-kit' ),
                    'content_width' => 'full',
                ],
            ],
            [
                'elType' => 'container',
                'settings' => [
                    '_title' => __( 'Slide #3', 'lastudio-kit' ),
                    'content_width' => 'full',
                ],
            ],
        ];
    }

	protected function get_default_repeater_title_setting_key() {
		return 'tab_title';
	}

    protected function get_default_children_title() {
        return esc_html__( 'Slide #%d', 'lastudio-kit' );
    }

    protected function get_default_children_placeholder_selector() {
        return '.swiper-wrapper';
    }

    protected function get_html_wrapper_class() {
        return 'lastudio-kit elementor-widget-lakit-n-carousel lakit-carousel-v2';
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_slides',
            [
                'label' => esc_html__( 'Slides', 'lastudio-kit' ),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'slide_title',
            [
                'label' => esc_html__( 'Title', 'lastudio-kit' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Slide Title', 'lastudio-kit' ),
                'placeholder' => esc_html__( 'Slide Title', 'lastudio-kit' ),
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'carousel_items',
            [
                'label' => esc_html__( 'Carousel Items', 'lastudio-kit' ),
                'type' => Control_Nested_Repeater::CONTROL_TYPE,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'slide_title' => esc_html__( 'Slide #1', 'lastudio-kit' ),
                    ],
                    [
                        'slide_title' => esc_html__( 'Slide #2', 'lastudio-kit' ),
                    ],
                    [
                        'slide_title' => esc_html__( 'Slide #3', 'lastudio-kit' ),
                    ],
                ],
                'title_field' => '{{{ slide_title }}}',
            ]
        );


        $this->end_controls_section();

        $this->register_carousel_section([], false, false);

        $this->update_responsive_control('carousel_columns', [
            'selectors'  => array(
                '{{WRAPPER}} .swiper-wrapper-{{ID}}' => '--e-c-col:{{VALUE}}',
            ),
            'frontend_available' => true,
        ]);
        $this->update_responsive_control('carousel_to_scroll', [
            'frontend_available' => true,
        ]);
        $this->update_responsive_control('carousel_rows', [
            'frontend_available' => true,
        ]);

        $css_scheme = apply_filters(
            'lastudio-kit/nested-carousel/css-schema',
            array(
                'column'         => '.lakit-carousel .swiper-slide--{{ID}}',
                'column_inner'   => '.lakit-carousel .swiper-slide--{{ID}} > .e-con',
                'column_inactive'=> '.lakit-carousel .swiper-slide--{{ID}}:not(.swiper-slide-active)',
            )
        );
        $this->_start_controls_section(
            'section_column_style',
            array(
                'label'      => esc_html__( 'Item', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );

        $this->_add_responsive_control(
            'item_alignment',
            array(
                'label'   => esc_html__( 'Alignment', 'lastudio-kit' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => array(
                    'top'    => array(
                        'title' => esc_html__( 'Top', 'lastudio-kit' ),
                        'icon'  => 'eicon-v-align-top',
                    ),
                    'center' => array(
                        'title' => esc_html__( 'Middle', 'lastudio-kit' ),
                        'icon'  => 'eicon-v-align-middle',
                    ),
                    'bottom' => array(
                        'title' => esc_html__( 'Bottom', 'lastudio-kit' ),
                        'icon'  => 'eicon-v-align-bottom',
                    ),
                ),
                'selectors_dictionary' => [
                    'top'    => 'justify-content: flex-start;',
                    'center' => 'justify-content: center;',
                    'bottom' => 'justify-content: flex-end;',
                ],
                'selectors'  => array(
                    '{{WRAPPER}} ' . $css_scheme['column_inner'] => '{{VALUE}}',
                ),
                'condition'  => array(
                    'carousel_direction' => 'vertical',
                ),
            ),
            25
        );

        $this->_add_responsive_control(
            'column_padding',
            array(
                'label'       => esc_html__( 'Item Margin', 'lastudio-kit' ),
                'type'        => Controls_Manager::DIMENSIONS,
                'size_units'  => ['px', '%', 'em', 'custom'],
                'render_type' => 'template',
                'selectors'   => array(
                    '{{WRAPPER}} ' . $css_scheme['column'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} ' => '--lakit-carousel-item-top-space: {{TOP}}{{UNIT}}; --lakit-carousel-item-right-space: {{RIGHT}}{{UNIT}};--lakit-carousel-item-bottom-space: {{BOTTOM}}{{UNIT}};--lakit-carousel-item-left-space: {{LEFT}}{{UNIT}};--lakit-gcol-top-space: {{TOP}}{{UNIT}}; --lakit-gcol-right-space: {{RIGHT}}{{UNIT}};--lakit-gcol-bottom-space: {{BOTTOM}}{{UNIT}};--lakit-gcol-left-space: {{LEFT}}{{UNIT}};',
                ),
            ),
            50
        );

        $this->_add_responsive_control(
            'column_margin',
            array(
                'label'       => esc_html__( 'Item Padding', 'lastudio-kit' ),
                'type'        => Controls_Manager::DIMENSIONS,
                'size_units'  => ['px', '%', 'em', 'custom'],
                'selectors'   => array(
                    '{{WRAPPER}} ' . $css_scheme['column_inner'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            ),
            50
        );

        $this->_add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'column_bg',
                'selector' => '{{WRAPPER}} ' . $css_scheme['column_inner']
            ),
            25
        );
        $this->_add_responsive_control(
            'column_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} ' . $css_scheme['column_inner'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            ),
            75
        );
        $this->_add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name'        => 'column_border',
                'label'       => esc_html__( 'Border', 'lastudio-kit' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} ' . $css_scheme['column_inner']
            ),
            50
        );
        $this->_add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'column_shadow',
                'selector' => '{{WRAPPER}} ' . $css_scheme['column_inner']
            ),
            75
        );

        $this->_end_controls_section();

        $this->_start_controls_section(
            'section_inactive_column_style',
            array(
                'label'      => esc_html__( 'Inactive Item', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'show_label' => false,
            )
        );
        $this->_add_responsive_control(
            'column_inactive_opacity',
            [
                'label' => esc_html__( 'Opacity', 'elementor' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} ' . $css_scheme['column_inactive'] => 'opacity: {{SIZE}};',
                ],
            ]
        );
        $this->_end_controls_section();

        $this->register_carousel_arrows_style_section([
                'carousel_arrows!' => ''
        ]);

        $this->dotv2_register_pagination_controls([
            'carousel_dots!' => ''
        ]);

    }


    protected function render() {
        $slides = $this->get_settings_for_display('carousel_items');
        $css_selector = sprintf('.swiper-wrapper-%1$s', esc_attr($this->get_id()));
        $css = lastudio_kit_helper()->get_css_by_responsive_columns( lastudio_kit_helper()->get_attribute_with_all_breakpoints('carousel_columns', $this->get_settings_for_display()), $css_selector );
        if(!empty($css)){
            echo sprintf('<style>%1$s</style>', $css); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        $slider_options = $this->get_advanced_carousel_options('carousel_columns');
        if(!empty($slider_options)){
            $this->add_render_attribute( 'main-container', 'class', 'lakit-carousel' );
            $this->add_render_attribute( 'main-container', 'data-slider_options', wp_json_encode($slider_options) );
            $this->add_render_attribute( 'main-container', 'dir', is_rtl() ? 'rtl' : 'ltr' );
            $this->add_render_attribute( 'list-wrapper', 'class', ['swiper-container', 'swiper-container-' . esc_attr($this->get_id())] );
            $carousel_id = $this->get_settings_for_display('carousel_id');
            if(empty($carousel_id)){
                $carousel_id = 'lakit_carousel_' . esc_attr($this->get_id());
            }
            $this->add_render_attribute( 'list-wrapper', 'id', $carousel_id );
            $this->add_render_attribute( 'list-container', 'class', ['swiper-wrapper', 'swiper-wrapper-' . esc_attr($this->get_id())] );
        }
        ?>
        <div <?php $this->print_render_attribute_string( 'main-container' ); ?>>
            <div class="lakit-carousel-inner">
                <div <?php $this->print_render_attribute_string( 'list-wrapper' ); ?>>
                    <div <?php $this->print_render_attribute_string( 'list-container' ); ?>>
                        <?php
                        foreach ( $slides as $index => $slide ) {
                            $slide_count = $index + 1;
                            $slide_setting_key = $this->get_repeater_setting_key( 'slide_wrapper', 'slide', $index );

                            $this->add_render_attribute( $slide_setting_key, [
                                'class' => 'swiper-slide swiper-slide--' . esc_attr($this->get_id()),
                                'data-slide' => $slide_count,
                            ] );
                            ?>
                            <div <?php $this->print_render_attribute_string( $slide_setting_key ); ?>>
                                <?php $this->print_child( $index ); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            if (filter_var($this->get_settings_for_display('carousel_dots'), FILTER_VALIDATE_BOOLEAN)) {
                echo '<div class="lakit-carousel__dots lakit-carousel__dots_'.esc_attr($this->get_id()).' swiper-pagination"></div>';
            }
            if (filter_var($this->get_settings_for_display('carousel_arrows'), FILTER_VALIDATE_BOOLEAN)) {
                echo sprintf('<div class="lakit-carousel__prev-arrow-%s lakit-arrow prev-arrow">%s</div>', esc_attr($this->get_id()), $this->_render_icon('carousel_prev_arrow', '%s', '', false)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo sprintf('<div class="lakit-carousel__next-arrow-%s lakit-arrow next-arrow">%s</div>', esc_attr($this->get_id()), $this->_render_icon('carousel_next_arrow', '%s', '', false)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            if (filter_var($this->get_settings_for_display('carousel_scrollbar'), FILTER_VALIDATE_BOOLEAN)) {
                echo sprintf('<div class="lakit-carousel__scrollbar swiper-scrollbar lakit-carousel__scrollbar_%1$s"></div>', esc_attr($this->get_id()));
            }
            ?>
        </div>
        <?php
    }

    protected function get_initial_config(): array {
        return array_merge( parent::get_initial_config(), [
            'support_improved_repeaters' => true,
            'target_container' => [ '.lakit-carousel > .lakit-carousel-inner > .swiper-container > .swiper-wrapper' ],
            'node' => 'div',
            'is_interlaced' => true,
        ] );
    }

    protected function get_default_children_container_placeholder_selector() {
        return '.swiper-slide';
    }

    protected function content_template_single_repeater_item() {
        ?>
        <#
        const elementUid = view.getIDInt().toString().substr( 0, 3 ),
        numOfSlides = view.collection.length + 1;

        const slideCount = numOfSlides,
        slideUid = elementUid + slideCount,
        slideWrapperKey = slideUid;

        const slideWrapperKeyItem = {
            'class': 'swiper-slide swiper-slide--' + view.getID(),
            'data-slide': slideCount
        };

        view.addRenderAttribute( 'single-slide', slideWrapperKeyItem, null, true );
        #>
        <div {{{ view.getRenderAttributeString( 'single-slide' ) }}}></div>
        <?php
    }

    protected function content_template() {
        ?>
        <# if ( settings['carousel_items'] ) {

        const elementUid = view.getIDInt().toString().substr( 0, 3 ),
        mainContainerKey = 'a1_carousel-' + elementUid,
        listWrapperKey = 'a2_carousel-' + elementUid,
        listContainerKey = 'a3_carousel-' + elementUid,
        shouldRenderPaginationAndArrows = 1 < settings['carousel_items'].length,
        carouselId = !! settings['carousel_id'] ? settings['carousel_id'] : 'lakit_carousel_' + view.getID();

        const carousel_direction = settings['carousel_direction'] === 'vertical' ? 'vertical' : 'horizontal';
        const isRTL = <?php echo is_rtl() ? "true" : "false" ?>;

        const getResponsiveValue = (_key, _settings) => {
            const _bpkArr = Object.keys(elementorFrontend.config.responsive.activeBreakpoints);
            _bpkArr.push('desktop')
            return _bpkArr.reduce(function(acc, cur) {
                acc[cur] = elementorFrontend.getDeviceSetting(cur, _settings, _key);
                return acc;
            }, {})
        }

        const filterVarBoolean = (_val) => {
            const truthyBooleanStrings = ['1', 'true', 'on', 'yes'];
            return truthyBooleanStrings.indexOf(_val) !== -1
        }

        const opts = {
            slidesToScroll: getResponsiveValue('carousel_to_scroll', settings),
            rows: carousel_direction === 'vertical' ? getResponsiveValue('carousel_rows', settings) : getResponsiveValue('carousel_rows', {}),
            direction: carousel_direction,
            autoplaySpeed: settings['carousel_autoplay_speed'],
            autoplay: filterVarBoolean(settings['carousel_autoplay']),
            infinite: filterVarBoolean(settings['carousel_loop']),
            centerMode: filterVarBoolean(settings['carousel_center_mode']),
            pauseOnHover: filterVarBoolean(settings['carousel_pause_on_hover']),
            pauseOnInteraction: filterVarBoolean(settings['carousel_pause_on_interaction']),
            reverseDirection: filterVarBoolean(settings['carousel_reverse_direction']),
            infiniteEffect: filterVarBoolean(settings['carousel_enable_linear_effect']),
            speed: settings['carousel_speed'],
            arrows: filterVarBoolean(settings['carousel_arrows']),
            dots: filterVarBoolean(settings['carousel_dots']),
            variableWidth: filterVarBoolean(settings['enable_swiper_item_auto_width']),
            prevArrow: '.lakit-carousel__prev-arrow-' + view.getID(),
            nextArrow: '.lakit-carousel__next-arrow-' + view.getID(),
            dotsElm: '.lakit-carousel__dots_' + view.getID(),
            rtl: isRTL,
            effect: settings['carousel_effect'],
            coverflowEffect: {
                rotate: settings['carousel_coverflow__rotate'],
                stretch: settings['carousel_coverflow__stretch'],
                depth: settings['carousel_coverflow__depth'],
                modifier: settings['carousel_coverflow__modifier'],
                scale: settings['carousel_coverflow__scale']
            },
            dotType: settings['carousel_dot_type'],
            uniqueID: view.getID(),
            asFor: settings['carousel_as_for'],
            thumbs: settings['carousel_thumbs'],
            autoHeight: filterVarBoolean(settings['carousel_autoheight']),
            scrollbar: filterVarBoolean(settings['carousel_scrollbar']),
            slidesToShow: getResponsiveValue('carousel_columns', settings),
        }

        if(filterVarBoolean(settings['enable_swiper_item_auto_width'])){
            view.addRenderAttribute( mainContainerKey, {
                'class': 'e-swiper--variablewidth',
            });
        }

        view.addRenderAttribute( mainContainerKey, {
            'class': 'lakit-carousel',
            'dir': isRTL ? 'rtl' : 'ltr',
            'data-slider_options': JSON.stringify(opts)
        });
        view.addRenderAttribute( listWrapperKey, {
            'class': 'swiper-container swiper-container-' + view.getID(),
            'id': carouselId
        });
        view.addRenderAttribute( listContainerKey, {
            'class': 'swiper-wrapper swiper-wrapper-' + view.getID()
        });

        #>
        <div {{{ view.getRenderAttributeString( mainContainerKey ) }}}>
            <div class="lakit-carousel-inner">
                <div {{{ view.getRenderAttributeString( listWrapperKey ) }}}>
                    <div {{{ view.getRenderAttributeString( listContainerKey ) }}}>
                        <# _.each( settings['carousel_items'], function( slide, index ) {
                        const slideCount = index + 1,
                        slideUid = elementUid + slideCount,
                        slideWrapperKey = slideUid;

                        view.addRenderAttribute( slideWrapperKey, {
                            'class': 'swiper-slide swiper-slide--' + view.getID(),
                            'data-slide': slideCount
                        } );
                        #>
                        <div {{{ view.getRenderAttributeString( slideWrapperKey ) }}}></div>
                        <# } ); #>
                    </div>
                </div>
            </div>
            <# if ( filterVarBoolean(settings['carousel_dots']) ) { #>
                <div class="lakit-carousel__dots lakit-carousel__dots_{{{ view.getID() }}} swiper-pagination"></div>
            <# } #>
            <# if ( filterVarBoolean(settings['carousel_arrows']) ) { #>
                <?php $this->content_template_navigation_arrows(); ?>
            <# } #>
            <# if ( filterVarBoolean(settings['carousel_scrollbar']) ) { #>
                <div class="lakit-carousel__scrollbar swiper-scrollbar lakit-carousel__scrollbar_{{{ view.getID() }}}"></div>
            <# } #>
        </div>
        <# } #>
        <?php
    }

    protected function content_template_navigation_arrows() {
        ?>
        <div class="lakit-carousel__prev-arrow-{{{ view.getID() }}} lakit-arrow prev-arrow">
            <#
            const iconSettingsPrevious = settings['selected_carousel_prev_arrow'],
            iconPreviousHTML = elementor.helpers.renderIcon( view, iconSettingsPrevious, { 'aria-hidden': true }, 'i' , 'object' );

            if ( 'lastudioicon-arrow-left' === iconSettingsPrevious['value'] ) { #>
            <?php Icons_Manager::render_icon(
                [
                    'library' => 'lastudioicon',
                    'value' => 'lastudioicon-arrow-left',
                ]
            ); ?>
            <# } else if ( !! iconSettingsPrevious['value'] ) { #>
            {{{ iconPreviousHTML.value }}}
            <# } #>
        </div>
        <div class="lakit-carousel__next-arrow-{{{ view.getID() }}} lakit-arrow next-arrow">
            <#
            const iconSettingsNext = settings['selected_carousel_next_arrow'],
            iconNextHTML = elementor.helpers.renderIcon( view, iconSettingsNext, { 'aria-hidden': true }, 'i' , 'object' );

            if ( 'lastudioicon-arrow-right' === iconSettingsNext['value'] ) { #>
            <?php Icons_Manager::render_icon(
                [
                    'library' => 'lastudioicon',
                    'value' => 'lastudioicon-arrow-right',
                ]
            ); ?>
            <# } else if ( !! iconSettingsNext['value'] ) { #>
            {{{ iconNextHTML.value }}}
            <# } #>
        </div>
        <?php
    }
}
