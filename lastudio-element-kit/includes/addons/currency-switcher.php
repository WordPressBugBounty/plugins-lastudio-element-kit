<?php

/**
 * Class: LaStudioKit_Currency_Switcher
 * Name: Currency Switcher
 * Slug: lakit-currency-switcher
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * LaStudioKit_Currency_Switcher Widget
 */
class LaStudioKit_Currency_Switcher extends LaStudioKit_Base {

    protected function enqueue_addon_resources(){
	    if(!lastudio_kit_settings()->is_combine_js_css()) {
		    if(!lastudio_kit()->is_optimized_css_mode()){
			    wp_register_style( 'lakit-curlang-switcher', lastudio_kit()->plugin_url( 'assets/css/addons/language-switcher.min.css' ), [], lastudio_kit()->get_version() );
			    $this->add_style_depends( 'lakit-curlang-switcher' );
			}
	    }
    }

	public function get_widget_css_config($widget_name){
		$file_url = lastudio_kit()->plugin_url( 'assets/css/addons/language-switcher.min.css' );
		$file_path = lastudio_kit()->plugin_path( 'assets/css/addons/language-switcher.min.css' );
		return [
			'key' => $widget_name,
			'version' => lastudio_kit()->get_version(true),
			'file_path' => $file_path,
			'data' => [
				'file_url' => $file_url
			]
		];
	}

    public function get_name() {
        return 'lakit-currency-switcher';
    }

    protected function get_widget_title() {
        return esc_html__( 'Currency Switcher', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'lastudio-kit-icon-currency-switcher';
    }

	public function get_keywords() {
		return [ 'currency', 'switcher', 'price'];
	}

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    protected function register_controls() {
        $this->_start_controls_section(
            'settings_section',
            [
                'label' => esc_html__('Settings', 'lastudio-kit'),
            ]
        );
        $this->_add_control(
            'type',
            [
                'label'   => esc_html__( 'Type', 'lastudio-kit' ),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => esc_html__( 'Dropdown', 'lastudio-kit' ),
                    'inline' => esc_html__( 'Inline', 'lastudio-kit' ),
                ],
                'default' => 'dropdown',
            ]
        );
        $this->_add_responsive_control(
            'direction',
            [
                'label' => esc_html__( 'Direction', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'row' => [
                        'title' => esc_html__( 'Row', 'lastudio-kit' ),
                        'icon' => 'eicon-arrow-right',
                    ],
                    'column' => [
                        'title' => esc_html__( 'Column', 'lastudio-kit' ),
                        'icon' => 'eicon-arrow-down',
                    ],
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'row' => '--e-switcher-direction: row;',
                    'column' => '--e-switcher-direction: column;',
                ],
                'condition' => [
                    'type' => 'inline',
                ]
            ]
        );
        $this->_add_control(
            'show_symbol',
            array(
                'label' => esc_html__('Show Symbol', 'lastudio-kit'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'lastudio-kit'),
                'label_off' => esc_html__('No', 'lastudio-kit'),
                'return_value' => 'yes',
                'default' => '',
            )
        );
        $this->_add_responsive_control(
            'symbol_pos',
            [
                'label' => esc_html__( 'Symbol Position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon' => 'eicon-order-start',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon' => 'eicon-order-end',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'left' => '--e-switcher-flag: -9;',
                    'right' => '--e-switcher-flag: 9;',
                ],
                'condition' => [
                    'show_symbol!' => '',
                ]
            ]
        );
        $this->_add_control(
            'show_fallback',
            array(
                'label' => esc_html__('Show Fallback', 'lastudio-kit'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'lastudio-kit'),
                'label_off' => esc_html__('No', 'lastudio-kit'),
                'return_value' => 'yes',
                'default' => '',
            )
        );
        $this->_end_controls_section();
        $this->_start_controls_section(
            'fallback_section',
            [
                'label' => esc_html__('Fallback', 'lastudio-kit'),
                'condition' => [
                    'show_fallback!' => '',
                ]
            ]
        );
        $repeater = new Repeater();
        $repeater->add_control(
            'code',
            [
                'label'   => esc_html__( 'Code', 'lastudio-kit' ),
                'type'    => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control(
            'symbol',
            [
                'label'   => esc_html__( 'Symbol', 'lastudio-kit' ),
                'type'    => Controls_Manager::TEXT,
            ]
        );
        $this->add_control(
            'custom_data',
            [
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ code }}} ({{{ symbol }}})',
            ]
        );
        $this->_end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label'      => esc_html__( 'General', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE
            ]
        );
        $this->add_responsive_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Items Gap', 'lastudio-kit' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [
                    'px', 'em',
                ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--e-switcher-item-gap: {{SIZE}}{{UNIT}};',
                ]
            ]
        );
        $this->_add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'font_size',
                'label'    => esc_html__( 'Typography', 'lastudio-kit' ),
                'selector' => '{{WRAPPER}} .switcher-flex-item',
            ]
        );
        $this->_add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__( 'Item Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .switcher-flex-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->_start_controls_tabs( '_item_tab_1' );
        $this->_start_controls_tab( 'item_normal',
            [
                'label' => esc_html__( 'Normal', 'lastudio-kit' ),
            ]
        );
        $this->_add_control(
            'item_color',
            array(
                'label' => esc_html__( 'Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-flex-item' => 'color: {{VALUE}}',
                ]
            )
        );
        $this->_end_controls_tab();
        $this->_start_controls_tab( 'item_hover',
            [
                'label' => esc_html__( 'Hover', 'lastudio-kit' ),
            ]
        );
        $this->_add_control(
            'item_color_hover',
            array(
                'label' => esc_html__( 'Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-flex-item:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .active .switcher-flex-item' => 'color: {{VALUE}}',
                ]
            )
        );
        $this->_add_control(
            'item_bg_color_hover',
            array(
                'label' => esc_html__( 'Background Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-flex-item:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .active .switcher-flex-item' => 'background-color: {{VALUE}}',
                ]
            )
        );
        $this->_end_controls_tab();
        $this->_end_controls_tabs();
        $this->_end_controls_section();
        $this->start_controls_section(
            'dropdown_section',
            [
                'label'      => esc_html__( 'Dropdown', 'lastudio-kit' ),
                'tab'        => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'type!' => 'inline',
                ]
            ]
        );
        $this->add_responsive_control(
            'dropdown_width',
            [
                'label'      => esc_html__( 'Dropdown Width', 'lastudio-kit' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [
                    'px', 'em',
                ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--e-switcher-dropdown-width: {{SIZE}}{{UNIT}};',
                ]
            ]
        );
        $this->_add_responsive_control(
            'dropdown_pos',
            [
                'label' => esc_html__( 'Dropdown Position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon' => 'eicon-order-start',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon' => 'eicon-order-end',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'left' => '--e-switcher-dropdown-left: 0;--e-switcher-dropdown-right: auto;',
                    'right' => '--e-switcher-dropdown-left: auto;--e-switcher-dropdown-right: 0;',
                ],
            ]
        );
        $this->_add_control(
            'dropdown_item_color',
            array(
                'label' => esc_html__( 'Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-dropdown .switcher-flex-item' => 'color: {{VALUE}}',
                ]
            )
        );
        $this->_add_control(
            'dropdown_item_hover_color',
            array(
                'label' => esc_html__( 'Hover Text Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-dropdown .switcher-flex-item:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .switcher-dropdown .active .switcher-flex-item' => 'color: {{VALUE}}',
                ]
            )
        );
        $this->_add_control(
            'dropdown_bg',
            array(
                'label' => esc_html__( 'Background', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .switcher-dropdown' => 'background-color: {{VALUE}}',
                ]
            )
        );
        $this->_add_responsive_control(
            'dropdown_padding',
            [
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .switcher-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->_add_responsive_control(
            'dropdown_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .switcher-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->_add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'dropdown_border',
                'label'       => esc_html__( 'Border', 'lastudio-kit' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector' => '{{WRAPPER}} .switcher-dropdown'
            ],
            50
        );
        $this->_add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'dropdown_shadow',
                'selector' =>  '{{WRAPPER}} .switcher-dropdown'
            ],
            75
        );
        $this->_end_controls_section();
    }

    protected function render() {

        $langs = $this->get_all_currencies();

        if(empty($langs)){
            $fallback_data = $this->get_settings_for_display('custom_data');
            if(!empty($fallback_data)){
                $current_url = add_query_arg(null, null);
                if(is_admin()){
                    $current_url = home_url();
                }
                $current_lang = $_GET['currency'] ?? '';
                foreach($fallback_data as $item){
                    $code = $item['code'] ?? '';
                    if(!empty($code)){
                        $langs[$code] = [
                            'name'   => $code,
                            'symbol'   => $item['symbol'] ?? '',
                            'url'    => add_query_arg('currency', strtolower($code), $current_url),
                            'active' => (strtolower($code) === strtolower($current_lang)),
                        ];
                    }
                }
            }
        }

        if(empty($langs)){
            return;
        }

        $type = $this->get_settings_for_display('type');
        $show_symbol = $this->get_settings_for_display('show_symbol');

        echo '<div class="lakit-curlang-switcher wcml_currency_switcher" data-type="'.esc_attr($type).'">';
        if($type !== 'inline'){
            $default = array_filter($langs, function( $item ){
                return $item['active'] === true;
            });
            if(!empty($default)){
                $default = current($default);
            }
            if(empty($default)){
                $default = current($langs);
            }
            $symbol_html = '';
            if($show_symbol && !empty($default['symbol'])){
                $symbol_html = sprintf('<span class="csymbol">(%1$s)</span>', esc_html($default['symbol']));
            }
            echo sprintf('<div class="switcher-item switcher-item--default switcher-flex-item"><span>%1$s</span>%2$s</div>', esc_html($default['name']), $symbol_html);
            echo '<div class="switcher-dropdown">';
        }
        foreach ($langs as $lang) {
            $class = ['switcher-item'];
            if($lang['active']){
                $class[] = 'active';
            }
            $symbol_html = '';
            if($show_symbol && !empty($lang['symbol'])){
                $symbol_html = sprintf('<span class="csymbol">(%1$s)</span>', esc_html($lang['symbol']));
            }
            if(empty($lang['url'])){
                echo '<div class="'.esc_attr(join(' ', $class)).'"><a class="switcher-flex-item woocs_auto_switcher_link" href="'.esc_url(home_url()).'" data-currency="'.esc_attr($lang['code']).'" rel="' . esc_attr($lang['code']) . '"><span>'.esc_html($lang['name']).'</span>' . $symbol_html . '</a></div>';
            }
            else{
                echo '<div class="'.esc_attr(join(' ', $class)).'"><a class="switcher-flex-item" href="' . esc_url($lang['url']) . '"><span>'.esc_html($lang['name']).'</span>' . $symbol_html . '</a></div>';
            }
        }
        if($type !== 'inline'){
            echo '</div>';
        }
        echo '</div>';
    }

    private function get_all_currencies()
    {

        if ( class_exists( '\WCML_Multi_Currency' ) ) {

            if(!function_exists('get_woocommerce_currency_symbol')){
                return [];
            }

            $multi_currency = new \WCML_Multi_Currency();
            $currencies         = $multi_currency->get_currencies(true);
            $active_currency    = $multi_currency->get_client_currency();
            $list = [];

            foreach ( $currencies as $code => $data ) {
                $symbol = get_woocommerce_currency_symbol($code);
                $list[$code] = [
                    'symbol'    => $symbol,
                    'name'      => $code,
                    'active'    => $code === $active_currency,
                    'code'      => $code,
                ];
            }

            return $list;
        }

        global $WOOCS;
        if (isset($WOOCS)) {
            $currencies = $WOOCS->get_currencies();
            $current    = $WOOCS->current_currency;
            $list = [];
            foreach ($currencies as $code => $data) {
                $list[$code] = [
                    'name'   => $data['name'],
                    'symbol' => $data['symbol'],
                    'code'   => $code,
                    'active' => ($code === $current),
                ];
            }
            return $list;
        }
        return [];
    }
}