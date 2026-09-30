<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'LaStudio_Kit_Integration' ) ) {

	/**
	 * Define LaStudio_Kit_Integration class
	 */
	class LaStudio_Kit_Integration {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

        public $sys_messages = [];

		/**
		 * Initalize integration hooks
		 *
		 * @return void
		 */
		public function init() {

			add_action( 'elementor/init', array( $this, 'register_category' ) );

			add_action( 'elementor/widgets/register', array( $this, 'register_addons' ), 10 );

			add_action( 'elementor/widgets/register', array( $this, 'register_vendor_addons' ), 20 );

            add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ) );

            add_action( 'elementor/editor/after_enqueue_styles',   array( $this, 'editor_styles' ) );

            add_filter( 'elementor/controls/animations/additional_animations', array( $this, 'register_custom_animation' ) );

            // WPML compatibility
            if ( defined( 'WPML_ST_VERSION' ) ) {
                add_filter( 'lastudio-kit/themecore/get_location_templates/template_id', array( $this, 'set_wpml_translated_location_id' ) );
            }

            // Polylang compatibility
            if ( class_exists( 'Polylang', false ) ) {
                add_filter( 'lastudio-kit/themecore/get_location_templates/template_id', array( $this, 'set_pll_translated_location_id' ) );
            }

            add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_link_fragments' ) );

            add_action( 'init', array( $this, 'register_handler' ) );
            add_action( 'init', array( $this, 'login_handler' ) );


            add_action( 'elementor/frontend/after_register_styles', [ $this, 'elementor_after_register_styles' ] );

            add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'frontend_enqueue_js' ), 20 );
            add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_later' ), 1000 );

            add_action( 'elementor/frontend/after_register_scripts', array( $this, 'frontend_override_js' ), 20 );

            // Set default single post template
            add_filter( 'get_post_metadata', array( $this, 'override_single_post_template' ), 10, 4 );

            add_action('lastudio-kit/ajax/register_actions', [ $this, 'register_ajax_actions' ] );

			add_filter( 'pre_get_posts', [ $this, 'setup_post_per_page_manager' ], 100);

            add_action( 'wp_head', [ $this, 'custom_head_code' ], 100 );
            add_action( 'wp_footer', [ $this, 'custom_footer_code' ], 100 );

            if(apply_filters('lastudio-kit/integration/allow-view-count', false)){
                add_action('template_redirect', [ $this, 'set_post_views_count' ], 100);
            }

            add_filter('elementor/widgets/black_list', [ $this, 'e_widget_blacklist' ], 20);
            add_filter('elementor/editor/localize_settings', [ $this, 'e_pro_widgets' ], 20);

            add_filter('elementor/shapes/additional_shapes', [ $this, 'e_shapes' ], 20);

            add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );

            add_filter( 'woocommerce_add_to_cart_redirect', [ $this, 'woocommerce_add_to_cart_redirect' ], 100, 1 );

            add_action( 'init', [ $this, 'on_init' ] );

            add_filter( 'body_class', [ $this, 'add_body_class' ], 100 );

            add_action('after_switch_theme', [ $this, 'after_switch_theme' ]);
        }

        public function after_switch_theme()
        {
            $cache_key = sprintf(
                'lakit_gmap_styles_%s',
                md5( get_template_directory() . get_stylesheet_directory() . lastudio_kit()->get_version(true) )
            );
            delete_transient( $cache_key );
        }

        public function on_init() {
            $this->sys_messages = apply_filters( 'lastudio-kit/popups_sys_messages', array(
                'invalid_mail'                => esc_html__( 'Please, provide valid mail', 'lastudio-kit' ),
                'mailchimp'                   => esc_html__( 'Please, set up MailChimp API key and List ID', 'lastudio-kit' ),
                'internal'                    => esc_html__( 'Internal error. Please, try again later', 'lastudio-kit' ),
                'server_error'                => esc_html__( 'Server error. Please, try again later', 'lastudio-kit' ),
                'invalid_nonce'               => esc_html__( 'Invalid nonce. Please, try again later', 'lastudio-kit' ),
                'subscribe_success'           => esc_html__( 'Success', 'lastudio-kit' ),
                'invalid_captcha'             => esc_html__( 'reCAPTCHA response token is invalid.', 'lastudio-kit' ),
                'require_field'               => esc_html__( 'Please fill out this field.', 'lastudio-kit' ),
            ) );
        }

		/**
		 * Check if we currently in Elementor mode
		 *
		 * @return boolean
		 */
		public function in_elementor() {

            $result = false;

            if ( wp_doing_ajax() ) {
                $result = ( isset( $_REQUEST['action'] ) && 'elementor_ajax' === $_REQUEST['action'] );
            } elseif ( Elementor\Plugin::instance()->editor->is_edit_mode() && 'wp_enqueue_scripts' === current_filter() ) {
                $result = true;
            } elseif ( Elementor\Plugin::instance()->preview->is_preview_mode() && 'wp_enqueue_scripts' === current_filter() ) {
                $result = true;
            }
			elseif ( !empty($_GET['elementor-preview']) ){
				$result = true;
			}
			elseif ( is_admin() && (!empty($_GET['action']) && $_GET['action'] === 'elementor')  ){
				$result = true;
			}

			/**
			 * Allow to filter result before return
			 *
			 * @var bool $result
			 */
			return apply_filters( 'lastudio-kit/in-elementor', $result );
		}

		/**
		 * Register plugin addons
		 *
		 * @param  object $widgets_manager Elementor widgets manager instance.
		 * @return void
		 */
		public function register_addons( $widgets_manager ) {
            $available_widgets = lastudio_kit_settings()->get_option( 'avaliable_widgets', [] );
            $registry = require lastudio_kit()->plugin_path( 'includes/addons/widget-registry.php' );
            foreach ( $registry as $slug => $widget ) {
                $enabled = $available_widgets[ $slug ] ?? false;
                if ( filter_var( $enabled, FILTER_VALIDATE_BOOLEAN ) || empty( $available_widgets ) ) {
                    $class = 'Elementor\\' . $widget['class'];
                    if(class_exists($class)){
                        $widgets_manager->register(new $class());
                    }
                }
            }
		}

		/**
		 * Register vendor addons
		 *
		 * @param  object $widgets_manager Elementor widgets manager instance.
		 * @return void
		 */
		public function register_vendor_addons( $widgets_manager ) {

            $woo_conditional = array(
                'cb'  => 'class_exists',
                'arg' => 'WooCommerce',
            );

            $allowed_vendors = apply_filters(
                'lastudio-kit/allowed-vendor-widgets',
                array(
                    'woo_products' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Products',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_menu_cart' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Menu_Cart',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_pages' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Pages',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_add_to_cart' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Add_To_Cart',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_title' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Title',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_images' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Images',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_price' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Price',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_addtocart' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Addtocart',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_rating' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Rating',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_stock' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Stock',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_meta' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Meta',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_shortdescription' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Shortdescription',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_content' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Content',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_datatabs' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Datatabs',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_single_product_additional_information' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Single_Product_Additional_Information',
                        'conditional' => $woo_conditional,
                    ),
                    'woo_wishlist_compare' => array(
                        'class' => 'Elementor\\LaStudioKit_Woo_Wishlist_Compare',
                        'conditional' => $woo_conditional,
                    ),
                    'contact_form7' => array(
                        'class' => 'Elementor\\LaStudioKit_Contact_Form7',
                        'conditional' => [
                            'cb'  => 'class_exists',
                            'arg' => 'WPCF7',
                        ],
                    ),
                )
            );

            foreach ( $allowed_vendors as $vendor ) {
                if (
                    is_callable( $vendor['conditional']['cb'] ) &&
                    true === call_user_func( $vendor['conditional']['cb'], $vendor['conditional']['arg'], false )
                )
                {
                    if(isset($vendor['file'])){
                        $this->register_addon( $vendor['file'], $widgets_manager );
                    }
                    else{
                        $class = $vendor['class'];
                        if(class_exists($class)){
                            $widgets_manager->register(new $class());
                        }
                    }

                }
            }
            if( lastudio_kit()->get_theme_support('elementor::product-grid-v2') && class_exists('WooCommerce', false) ){
                $class = 'Elementor\\LaStudioKit_Woo_Filters';
                if(class_exists($class)){
                    $widgets_manager->register(new $class());
                }
            }
		}

		/**
		 * Register addon by file name
		 *
		 * @param  string $file            File name.
		 * @param  object $widgets_manager Widgets manager instance.
		 * @return void
		 */
        public function register_addon( string $file, $widgets_manager ): void
        {
            static $classCache = [];
            if (!isset($classCache[$file])) {
                $classCache[$file] = 'Elementor\\LaStudioKit_' . str_replace(' ','_',ucwords(str_replace('-', ' ', pathinfo($file, PATHINFO_FILENAME))));
            }
            $class = $classCache[$file];
            if (!class_exists($class)) {
                return;
            }
            $widgets_manager->register(new $class());
        }

		/**
		 * Register cherry category for elementor if not exists
		 *
		 * @return void
		 */
		public function register_category() {

            Elementor\Plugin::instance()->elements_manager->add_category(
                'lastudiokit-builder',
                array(
                    'title' => esc_html__( 'LaStudio Kit Builder', 'lastudio-kit' ),
                    'icon'  => 'font',
                )
            );

            Elementor\Plugin::instance()->elements_manager->add_category(
				'lastudiokit',
				array(
					'title' => esc_html__( 'LaStudio Kit', 'lastudio-kit' ),
					'icon'  => 'font',
				)
			);

            Elementor\Plugin::instance()->elements_manager->add_category(
				'lastudiokit-woocommerce',
				array(
					'title' => esc_html__( 'LaStudio Kit WooCommerce', 'lastudio-kit' ),
					'icon'  => 'font',
				)
			);

            Elementor\Plugin::instance()->elements_manager->add_category(
				'lastudiokit-woo-product',
				array(
					'title' => esc_html__( 'LaStudio Kit Product', 'lastudio-kit' ),
					'icon'  => 'font',
				)
			);

		}

        /**
         * Enqueue plugin scripts only with elementor scripts
         *
         * @return void
         */
        public function editor_scripts() {

            wp_enqueue_script(
                'lastudio-kit-editor',
                lastudio_kit()->plugin_url( 'assets/js/lastudio-kit-editor.js' ),
                array( 'jquery' ),
                lastudio_kit()->get_version(),
                true
            );
        }

        /**
         * Enqueue editor styles
         *
         * @return void
         */
        public function editor_styles() {
            wp_enqueue_style(
                'lastudio-kit-editor',
                lastudio_kit()->plugin_url( 'assets/css/lastudio-kit-editor.css' ),
                [],
                lastudio_kit()->get_version()
            );
        }

        public function elementor_after_register_styles()
        {
            wp_register_style( 'lastudio-kit-base', lastudio_kit()->plugin_url('assets/css/lastudio-kit-base.min.css'), ['elementor-frontend'], lastudio_kit()->get_version());

            $css_widget_lists = [
                'lastudio-kit-player' => 'assets/css/addons/player.min.css',
                'lastudio-kit-swiper-dotv2' => 'assets/css/addons/n-carousel.min.css',
                'lakit-advanced-carousel' => 'assets/css/addons/advanced-carousel.min.css',
                'lakit-banner' => 'assets/css/addons/banner.min.css',
                'lakit-animated-box' => 'assets/css/addons/animated-box.min.css',
                'lakit-animated-text' => 'assets/css/addons/animated-text.min.css',
                'lakit-author-box' => 'assets/css/addons/author-box.min.css',
                'lakit-banner-list' => 'assets/css/addons/banner-list.min.css',
                'lakit-breadcrumbs' => 'assets/css/addons/breadcrumbs.min.css',
                'lakit-counter' => 'assets/css/addons/counter.min.css',
                'lakit-curlang-switcher' => 'assets/css/addons/language-switcher.min.css',
                'lakit-google-maps' => 'assets/css/addons/google-map.min.css',
                'lakit-hamburger-panel' => 'assets/css/addons/hamburger-panel.min.css',
                'lakit-hotspots' => 'assets/css/addons/hotspots.min.css',
                'lakit-icon-box' => 'assets/css/addons/iconbox.min.css',
                'lakit-image-box' => 'assets/css/addons/imagebox.min.css',
                'lakit-image-compare' => 'assets/css/addons/image-compare.min.css',
                'lakit-images-layout' => 'assets/css/addons/images-layout.min.css',
                'lakit-instagram-feed' => 'assets/css/addons/instagram-feed.min.css',
                'lakit-login-frm' => 'assets/css/addons/login.min.css',
                'lastudio-kit-dlmenu' => 'assets/css/lastudio-dlmenu.min.css',
                'lakit-nav-menu' => 'assets/css/addons/nav-menu.min.css',
                'lakit-post-navigation' => 'assets/css/addons/post-navigation.min.css',
                'lakit-posts' => 'assets/css/addons/posts.min.css',
                'lakit-price-list' => 'assets/css/addons/price-list.min.css',
                'lakit-pricing-table' => 'assets/css/addons/pricing-table.min.css',
                'lakit-progress-bar' => 'assets/css/addons/progress-bar.min.css',
                'lakit-register-frm' => 'assets/css/addons/register.min.css',
                'lakit-search' => 'assets/css/addons/search.min.css',
                'lakit-slides' => 'assets/css/addons/slides.min.css',
                'lakit-social-share' => 'assets/css/addons/social-share.min.css',
                'lakit-subscribe-form' => 'assets/css/addons/subscribe-form.min.css',
                'lakit-table-of-contents' => 'assets/css/addons/tablet-contents.min.css',
                'lakit-tabs' => 'assets/css/addons/tabs.min.css',
                'lakit-team-member' => 'assets/css/addons/team-member.min.css',
                'lakit-testimonials' => 'assets/css/addons/testimonials.min.css',
                'lakit-timeline-horizontal' => 'assets/css/addons/timeline-horizontal.min.css',
                'lakit-timeline-vertical' => 'assets/css/addons/timeline-vertical.min.css',
                'lakit-twitter' => 'assets/css/addons/twitter.min.css',
                'lakit-album-lists' => 'assets/css/addons/album-lists.min.css',
                'lakit-events' => 'assets/css/addons/events.min.css',
                'lakit-givewp' => 'assets/css/addons/givewp.min.css',
                'lakit-portfolio' => 'assets/css/addons/portfolio.min.css',
                'lakit-nested-tabs' => 'assets/css/addons/n-tabs.min.css'
            ];
            if ( class_exists( 'WooCommerce', false ) ) {
                $css_widget_lists['lastudio-kit-woocommerce'] = 'assets/css/lastudio-kit-woocommerce.min.css';
            }
            foreach ( $css_widget_lists as $handle_name => $css_url ) {
                wp_register_style( $handle_name, lastudio_kit()->plugin_url($css_url), ['lastudio-kit-base'], lastudio_kit()->get_version());
            }

            if( apply_filters( 'lastudio-kit/allow_override_elementor_device', true ) ){
                wp_add_inline_style('elementor-frontend', $this->set_device_name_for_custom_bkp_by_css());
            }
        }

        public function frontend_enqueue_js(){

	        $polyfill_data = apply_filters('lastudio-kit/filter/js_polyfill_data', [
		        'lakit-polyfill-resizeobserver' => [
			        'condition' => '\'ResizeObserver\' in window',
			        'src'       => lastudio_kit()->plugin_url( 'assets/js/lib/polyfill-resizeobserver.min.js' ),
			        'version'   => '1.5.0',
		        ],
	        ]);
	        $polyfill_inline = lastudio_kit_helper()->get_polyfill_inline( $polyfill_data );

            $disable_gmap_api_js    = lastudio_kit_settings()->get_option('disable_gmap_api_js');
            $google_api_key         = lastudio_kit_settings()->get_option('gmap_api_key', lastudio_kit_settings()->get_option('gmap_backend_api_key'));
            if (!filter_var($disable_gmap_api_js, FILTER_VALIDATE_BOOLEAN) && !empty($google_api_key)) {
                $google_api_url = add_query_arg(
                    array(
                        'key' => $google_api_key,
                        'libraries' => 'marker'
                    ),
                    'https://maps.googleapis.com/maps/api/js'
                );
                wp_register_script('lastudio-kit-gmap_api', $google_api_url, false, lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ]);
            }

	        wp_register_script( 'lastudio-kit-anime-js', lastudio_kit()->plugin_url( 'assets/js/lib/anime.min.js' ), [], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script(  'jquery-isotope' , lastudio_kit()->plugin_url('assets/js/lib/isotope.pkgd.min.js') , [],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script(  'embla-carousel' , lastudio_kit()->plugin_url('assets/js/lib/embla-carousel.min.js') , [],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script(  'odometer' , lastudio_kit()->plugin_url('assets/js/lib/odometer.min.js') , [],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script( 'lastudio-kit-dlmenu', lastudio_kit()->plugin_url( 'assets/js/lib/lakitdlmenu.min.js' ), [], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script(  'lastudio-kit-player' , lastudio_kit()->plugin_url('assets/js/addons/player.min.js') , ['jquery'],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );

	        wp_register_script( 'lastudio-kit-floating-effects', lastudio_kit()->plugin_url('assets/js/addons/floating-effects.min.js'), ['lastudio-kit-anime-js', 'elementor-frontend'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ]);
	        wp_register_script(  'lastudio-kit-sticky-js' , lastudio_kit()->plugin_url('assets/js/lib/jquery.sticky.min.js') , ['jquery'],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script(  'lastudio-kit-motion-fx' , lastudio_kit()->plugin_url('assets/js/addons/motion-fx.min.js') , ['lastudio-kit-sticky-js'],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script( 'lastudio-kit-wrapper-links', lastudio_kit()->plugin_url('assets/js/addons/wrapper-links.min.js'), [], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ]);

            wp_register_script(  'lastudio-kit-base' , lastudio_kit()->plugin_url('assets/js/lastudio-kit-base.min.js') , [ 'elementor-frontend' ],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script(  'lastudio-kit-header-vertical' , lastudio_kit()->plugin_url('assets/js/addons/header-sidebar.min.js') , [ 'lastudio-kit-base' ],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_add_inline_script('lastudio-kit-header-vertical', $polyfill_inline, 'before');

            wp_register_script( 'lastudio-kit-w__tabs', lastudio_kit()->plugin_url( 'assets/js/addons/tabs.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__ntabs', lastudio_kit()->plugin_url( 'assets/js/addons/n-tabs.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__hotspots', lastudio_kit()->plugin_url( 'assets/js/addons/hotspots.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__social-share', lastudio_kit()->plugin_url( 'assets/js/addons/social-share.js' ), [ 'share-link' ], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__tableofcontent', lastudio_kit()->plugin_url( 'assets/js/addons/tablet-contents.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__htimeline', lastudio_kit()->plugin_url( 'assets/js/addons/timeline-horizontal.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__vtimeline', lastudio_kit()->plugin_url( 'assets/js/addons/timeline-vertical.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__twitter', lastudio_kit()->plugin_url( 'assets/js/addons/twitter.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__animated-text', lastudio_kit()->plugin_url( 'assets/js/addons/animated-text.min.js' ), ['lastudio-kit-base', 'lastudio-kit-anime-js'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__progress-bar', lastudio_kit()->plugin_url( 'assets/js/addons/progress-bar.js' ), ['lastudio-kit-base', 'lastudio-kit-anime-js'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__counter', lastudio_kit()->plugin_url( 'assets/js/addons/counter.min.js' ), ['lastudio-kit-base', 'odometer'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__image-compare', lastudio_kit()->plugin_url( 'assets/js/addons/image-compare.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lakit-subscribe-form', lastudio_kit()->plugin_url( 'assets/js/addons/subscribe-form.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
	        wp_register_script( 'lastudio-kit-w__gmap', lastudio_kit()->plugin_url( 'assets/js/addons/google-map.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script( 'lastudio-kit-w__ncarousel', lastudio_kit()->plugin_url( 'assets/js/addons/n-carousel.min.js' ), ['lastudio-kit-base'], lastudio_kit()->get_version(), [ 'in_footer' => true, 'strategy' => 'defer' ] );
            wp_register_script(  'lastudio-kit-w__buynow' , lastudio_kit()->plugin_url('assets/js/addons/buynow.min.js') , ['jquery', 'lastudio-kit-base'],  lastudio_kit()->get_version() , [ 'in_footer' => true, 'strategy' => 'defer' ] );

            $rest_api_url = apply_filters( 'lastudio-kit/rest/frontend/url', get_rest_url() );

            $template_cache = filter_var(lastudio_kit_settings()->get_option('template-cache', false), FILTER_VALIDATE_BOOLEAN);

			$delayResources = [
				'js' => [],
				'css' => [],
			];
			if(class_exists( 'Give', false )){
				$delayResources['js']['lakit-givewp'] = lastudio_kit()->plugin_url('assets/js/delay/givewp.min.js');
			}
            if(class_exists( 'WooCommerce', false )){
                if(lastudio_kit()->get_theme_support('lakit-swatches') && 'yes' !== lastudio_kit_settings()->get('swatches__is_disable', '')){
                    $delayResources['js']['swatches'] = lastudio_kit()->plugin_url('includes/extensions/swatches/assets/js/swatches.min.js');
                }
            }

            $LaStudioKitSettings = [
                'version'        => lastudio_kit()->get_version(),
	            'templateApiUrl' => $rest_api_url . 'lastudio-kit-api/v1/elementor-template',
	            'widgetApiUrl'   => $rest_api_url . 'lastudio-kit-api/v1/elementor-widget',
	            'homeURL'        => esc_url(home_url('/')),
	            'ajaxUrl'        => esc_url( admin_url( 'admin-ajax.php' ) ),
	            'isMobile'       => filter_var( wp_is_mobile(), FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false',
	            'devMode'        => !$template_cache ? 'true' : 'false',
	            'isDebug'        => ( defined('WP_DEBUG') && WP_DEBUG ) ? true : false,
	            'cache_ttl'      => apply_filters('lastudio-kit/cache-management/time-to-life', !$template_cache ? 30 : (60 * 30)),
	            'local_ttl'      => apply_filters('lastudio-kit/cache-management/local-time-to-life', !$template_cache ? 120 : (60 * 60 * 24)),
	            'themeName'      => get_template(),
	            'i18n'           => [
                    'swatches_more_text'    => lastudio_kit_settings()->get('swatches_swatches_more_text', ''),
                    'swatches_max_item'     => lastudio_kit_settings()->get('swatches_swatches_max_item', ''),
                    'cart_group_msg'        => lastudio_kit_settings()->get('cart_group_msg', esc_html__( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'woocommerce' ))
                ],
	            'ajaxNonce'      => lastudio_kit()->ajax_manager->create_nonce(),
	            'restNonce'      => wp_create_nonce('wp_rest'),
	            'useFrontAjax'   => 'true',
                'isElementorAdmin' => lastudio_kit()->elementor()->editor->is_edit_mode() || lastudio_kit()->elementor()->preview->is_preview_mode(),
                'resources'      => [
                    'imagesloaded'      => lastudio_kit()->plugin_url('assets/js/lib/imagesloaded.min.js'),
                    'jquery-isotope'    => lastudio_kit()->plugin_url('assets/js/lib/isotope.pkgd.min.js'),
                    'embla-carousel'    => lastudio_kit()->plugin_url('assets/js/lib/embla-carousel.min.js'),
                    'bootstrap-tooltip' => lastudio_kit()->plugin_url('assets/js/lib/bootstrap-tooltip.min.js'),
                    'spritespin'        => lastudio_kit()->plugin_url('assets/js/lib/spritespin.min.js'),
	                'popupjs'           => lastudio_kit()->plugin_url('includes/modules/popup/assets/js/popup.min.js'),
	                'popupcss'          => lastudio_kit()->plugin_url('includes/modules/popup/assets/css/popup.min.css'),
                    'odometerjs'        => lastudio_kit()->plugin_url('assets/js/lib/odometer.min.js'),
                    'swiperjs'          => lastudio_kit()->plugin_url('assets/js/lib/swiper.min.js'),
                    'simpleparallax'    => lastudio_kit()->plugin_url('assets/js/lib/simple-parallax.min.js'),
                    'subscribeForm'     => lastudio_kit()->plugin_url('assets/js/addons/subscribe-form.min.js'),
                ],
	            'delayResources' => $delayResources,
	            'recaptchav3'    => $this->is_active_recaptchav3() ? $this->get_recaptchav3_site_key() : '',
            ];

            if(class_exists( 'Give', false )){
                $LaStudioKitSettings['giveRouter'] = esc_url(add_query_arg(['givewp-route'=>'donation-form-view', 'form-id' => 'lakit_rp_lakit'], home_url('/')));
            }

            wp_localize_script('lastudio-kit-base', 'LaStudioKitSettings', $LaStudioKitSettings );

            $subscribe_obj = [
                'action' => 'lakit_ajax',
                'nonce' => lastudio_kit()->ajax_manager->create_nonce(),
                'type' => 'POST',
                'data_type' => 'json',
                'is_public' => 'true',
                'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
                'sys_messages' => $this->sys_messages
            ];
            wp_localize_script( 'elementor-frontend', 'lakitSubscribeConfig', $subscribe_obj );
            wp_localize_script( 'lakit-subscribe-form', 'lakitSubscribeConfig', $subscribe_obj );
        }

        public function frontend_enqueue_later(){

            $disable_gutenberg_block_css = lastudio_kit_settings()->get_option('disable-gutenberg-block', '');

			if($disable_gutenberg_block_css === 'enabled'){
                wp_dequeue_style( 'wp-block-library' );
                wp_dequeue_style( 'wp-block-library-theme' );
                wp_dequeue_style( 'classic-theme-styles' );
                wp_deregister_style( 'wc-blocks-style' );
                wp_dequeue_style( 'wc-blocks-style' );
                wp_dequeue_style( 'wc-all-blocks-style' );
                wp_deregister_script('wp-embed');
                wp_deregister_script('thickbox');
                wp_dequeue_style('thickbox');
			}
            $css = '@media screen and ( max-width: 782px ) { html { margin-top: 0 !important; } }';
            wp_add_inline_style( 'admin-bar', $css );
        }

        public function frontend_override_js()
        {
            wp_deregister_script('swiper');
            wp_register_script(
                'swiper',
                lastudio_kit()->plugin_url('assets/js/lib/swiper.min.js'),
                [],
                '8.4.5',
                true
            );
        }

        /**
         * Set WPML translated location.
         *
         * @param $post_id
         *
         * @return mixed|void
         */
        public function set_wpml_translated_location_id( $post_id ) {
            $location_type = get_post_type( $post_id );

            return apply_filters( 'wpml_object_id', $post_id, $location_type, true );
        }

        /**
         * set_pll_translated_location_id
         *
         * @param $post_id
         *
         * @return false|int|null
         */
        public function set_pll_translated_location_id( $post_id ) {

            if ( function_exists( 'pll_get_post' ) ) {

                $translation_post_id = pll_get_post( $post_id );

                if ( null === $translation_post_id ) {
                    // the current language is not defined yet
                    return $post_id;
                } elseif ( false === $translation_post_id ) {
                    //no translation yet
                    return $post_id;
                } elseif ( $translation_post_id > 0 ) {
                    // return translated post id
                    return $translation_post_id;
                }
            }

            return $post_id;
        }

        /**
         * Cart link fragments
         *
         * @return array
         */
        public function cart_link_fragments( $fragments ) {

            global $woocommerce;

            $lakit_fragments = apply_filters( 'lastudio-kit/handlers/cart-fragments', array(
                '.lakit-cart__total-val' => 'menucart/global/cart-totals.php',
                '.lakit-cart__count-val' => 'menucart/global/cart-count.php',
            ) );

            foreach ( $lakit_fragments as $selector => $template ) {
                ob_start();
                include lastudio_kit()->get_template( $template );
                $fragments[ $selector ] = ob_get_clean();
            }

            return $fragments;

        }


        /**
         * Login form handler.
         *
         * @return void
         */
        public function login_handler() {

            if ( ! isset( $_POST['lakit_login'] ) ) {
                return;
            }

            try {

                if ( empty( $_POST['log'] ) ) {

                    $error = sprintf(
                        '<strong>%1$s</strong>: %2$s',
                        __( 'ERROR', 'lastudio-kit' ),
                        __( 'The username field is empty.', 'lastudio-kit' )
                    );

                    throw new Exception( $error );

                }

                $signon = wp_signon();

                if ( is_wp_error( $signon ) ) {
                    throw new Exception( $signon->get_error_message() );
                }

                $redirect = isset( $_POST['redirect_to'] )
                    ? esc_url( $_POST['redirect_to'] )
                    : esc_url( home_url( '/' ) );

                wp_redirect( $redirect );
                exit;

            } catch ( Exception $e ) {
                wp_cache_set( 'lakit-login-messages', $e->getMessage(), 'lastudio-kit', 5 * MINUTE_IN_SECONDS );
            }

        }

        /**
         * Registration handler
         *
         * @return void
         */
        public function register_handler() {

            if ( ! isset( $_POST['lakit-register-nonce'] ) ) {
                return;
            }

            if ( ! wp_verify_nonce( $_POST['lakit-register-nonce'], 'lakit-register' ) ) {
                return;
            }

            try {

                $username           = isset( $_POST['username'] ) ? $_POST['username'] : '';
                $password           = isset( $_POST['password'] ) ? $_POST['password'] : '';
                $email              = isset( $_POST['email'] ) ? $_POST['email'] : '';
                $confirm_password   = isset( $_POST['lakit_confirm_password'] ) ? $_POST['lakit_confirm_password'] : '';
                $confirmed_password = isset( $_POST['password-confirm'] ) ? $_POST['password-confirm'] : '';
                $confirm_password   = filter_var( $confirm_password, FILTER_VALIDATE_BOOLEAN );

                if ( $confirm_password && $password !== $confirmed_password ) {
                    throw new Exception( esc_html__( 'Entered passwords don\'t match', 'lastudio-kit' ) );
                }

                $user = $this->create_user( $username, sanitize_email( $email ), $password );

                if ( is_wp_error( $user ) ) {
                    throw new Exception( $user->get_error_message() );
                }

                global $current_user;
                $current_user = get_user_by( 'id', $user );
                wp_set_auth_cookie( $user, true );

                if ( ! empty( $_POST['lakit_redirect'] ) ) {
                    $redirect = wp_sanitize_redirect( $_POST['lakit_redirect'] );
                } else {
                    $redirect = $_POST['_wp_http_referer'];
                }

                wp_redirect( $redirect );
                exit;

            } catch ( Exception $e ) {
                wp_cache_set( 'lakit-register-messages', $e->getMessage(), 'lastudio-kit', 5 * MINUTE_IN_SECONDS );
            }

        }

        /**
         * Create new user function
         *
         * @param  [type] $username [description]
         * @param  [type] $email    [description]
         * @param  [type] $password [description]
         * @return [type]           [description]
         */
        public function create_user( $username, $email, $password ) {

	        if('yes' !== get_option( 'woocommerce_enable_myaccount_registration' ) && 'yes' !== get_option( 'users_can_register' )){
		        return new WP_Error(
			        'registration-error-disable',
			        __( 'This feature is not available.', 'lastudio-kit' )
		        );
	        }

            // Check username
            if ( empty( $username ) || ! validate_username( $username ) ) {
                return new WP_Error(
                    'registration-error-invalid-username',
                    __( 'Please enter a valid account username.', 'lastudio-kit' )
                );
            }

            if ( username_exists( $username ) ) {
                return new WP_Error(
                    'registration-error-username-exists',
                    __( 'An account is already registered with that username. Please choose another.', 'lastudio-kit' )
                );
            }

            // Check the email address.
            if ( empty( $email ) || ! is_email( $email ) ) {
                return new WP_Error(
                    'registration-error-invalid-email',
                    __( 'Please provide a valid email address.', 'lastudio-kit' )
                );
            }

            if ( email_exists( $email ) ) {
                return new WP_Error(
                    'registration-error-email-exists',
                    __( 'An account is already registered with your email address. Please log in.', 'lastudio-kit' )
                );
            }

            // Check password
            if ( empty( $password ) ) {
                return new WP_Error(
                    'registration-error-missing-password',
                    __( 'Please enter an account password.', 'lastudio-kit' )
                );
            }

            $custom_error = apply_filters( 'lakit_register_form_custom_error', null );

            if ( is_wp_error( $custom_error ) ){
                return $custom_error;
            }

            $new_user_data = array(
                'user_login' => $username,
                'user_pass'  => $password,
                'user_email' => $email,
            );

            $user_id = wp_insert_user( $new_user_data );

            if ( is_wp_error( $user_id ) ) {
                return new WP_Error(
                    'registration-error',
                    '<strong>' . __( 'Error:', 'lastudio-kit' ) . '</strong> ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'lastudio-kit' )
                );
            }

            return $user_id;

        }

		/**
		 *
		 * @see wc_create_new_customer_username
		 * @param $email
		 * @param $new_user_args
		 * @param $suffix
		 *
		 * @return string Generated username.
		 */
		public static function generate_username( $email, $new_user_args = array(), $suffix = '' ){

			if(function_exists('wc_create_new_customer_username')){
				return wc_create_new_customer_username( $email, $new_user_args, $suffix );
			}

			$username_parts = array();

			if ( isset( $new_user_args['first_name'] ) ) {
				$username_parts[] = sanitize_user( $new_user_args['first_name'], true );
			}

			if ( isset( $new_user_args['last_name'] ) ) {
				$username_parts[] = sanitize_user( $new_user_args['last_name'], true );
			}

			// Remove empty parts.
			$username_parts = array_filter( $username_parts );

			// If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
			if ( empty( $username_parts ) ) {
				$email_parts    = explode( '@', $email );
				$email_username = $email_parts[0];

				// Exclude common prefixes.
				if ( in_array(
					$email_username,
					array(
						'sales',
						'hello',
						'mail',
						'contact',
						'info',
					),
					true
				) ) {
					// Get the domain part.
					$email_username = $email_parts[1];
				}

				$username_parts[] = sanitize_user( $email_username, true );
			}

			$username = strtolower( implode( '.', $username_parts ) );
			if ( $suffix ) {
				$username .= $suffix;
			}
			$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );
			// Stop illegal logins and generate a new random username.
			if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ), true ) ) {
				$new_args = array();
				$new_args['first_name'] = apply_filters(
					'woocommerce_generated_customer_username',
					'woo_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
					$email,
					$new_user_args,
					$suffix
				);
				return self::generate_username( $email, $new_args, $suffix );
			}
			if ( username_exists( $username ) ) {
				// Generate something unique to append to the username in case of a conflict with another user.
				$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
				return self::generate_username( $email, $new_user_args, $suffix );
			}
			return apply_filters( 'woocommerce_new_customer_username', $username, $email, $new_user_args, $suffix );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return LaStudio_Kit_Integration
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function set_device_name_for_custom_bkp_by_css(){
		    $breakpoints = lastudio_kit_helper()->get_active_breakpoints();
			asort($breakpoints);
		    $css = '.e-con .lastudio-kit.elementor-widget.elementor-element{--eew: min(calc(var(--e-w-ml, 0px) + var(--e-w-mr, 0px)), 0px);max-width:calc(100% - var(--eew, 0px))}';

		    $grid_mapping = [
		        'laptop'        => 'desk',
		        'tablet'        => 'lap',
		        'mobile_extra'  => 'tab',
		        'tabletportrait'=> 'tab',
                'mobile'        => 'tabp'
            ];

            $grid_mapping2 = [
                'mob',
                'tabp',
                'tab',
                'lap',
                'desk'
            ];

            if(!isset($breakpoints['laptop'])){
                $grid_mapping['tablet'] = 'desk';
            }
            if(!isset($breakpoints['mobile_extra']) && !isset($breakpoints['tabletportrait'])){
                $grid_mapping['mobile'] = 'tab';
            }

            $tmpgrid = [];
            foreach ($grid_mapping2 as $v){
                $tmpgrid[] = sprintf('[class*="col-%1$s-"]', $v);
            }

            $css .= join(',', $tmpgrid) . '{--gw:inherit;--gfw:0 0 var(--gw);position:relative;min-height:1px;padding:10px;box-sizing:border-box;width:100%;flex:var(--gfw);max-width:var(--gw)}';

            for ( $j = 1; $j <= 10; $j++ ){
                $css .= sprintf('.col-%1$s-%2$s{--gw:calc(%3$s)}', 'mob', $j, '100%/' . $j);
            }

			foreach ($breakpoints as $device_name => $device_value){
				if( array_key_exists($device_name, $grid_mapping) ){
					$css .= '@media(min-width:'.($device_value+1).'px){';
					for ( $j = 1; $j <= 10; $j++ ){
						$css .= sprintf('.col-%1$s-%2$s{--gw:calc(%3$s)}', $grid_mapping[$device_name], $j, '100%/' . $j);
					}
					$css .= '}';
				}
			}

			arsort($breakpoints);

			$column_css = '.elementor-element.lakit-col-width-auto-__DEVICE__{width:auto!important}.elementor-element.lakit-col-width-auto-__DEVICE__.lakit-col-align-left{margin-right:auto}.elementor-element.lakit-col-width-auto-__DEVICE__.lakit-col-align-right{margin-left:auto}.elementor-element.lakit-col-width-auto-__DEVICE__.lakit-col-align-center{margin-left:auto;margin-right:auto}';
			if ( ! lastudio_kit()->elementor()->experiments->is_feature_active( 'container' ) ) {
				$widget_align_desktop_css = '[data-elementor-device-mode=desktop] .lakit-widget-align-left{margin-right:auto!important}[data-elementor-device-mode=desktop] .lakit-widget-align-right{margin-left:auto!important}[data-elementor-device-mode=desktop] .lakit-widget-align-center{margin-left:auto!important;margin-right:auto!important}';
				$widget_align_css         = '[data-elementor-device-mode=__DEVICE__] .lakit-widget-align-__DEVICE__-left{margin-right:auto!important}[data-elementor-device-mode=__DEVICE__] .lakit-widget-align-__DEVICE__-right{margin-left:auto!important}[data-elementor-device-mode=__DEVICE__] .lakit-widget-align-__DEVICE__-center{margin-left:auto!important;margin-right:auto!important}';
			}
			else{
				$widget_align_desktop_css = $widget_align_css = '';
			}

			$css .= $widget_align_desktop_css;
			foreach ($breakpoints as $device_name => $device_value){
				$css .= str_replace('__DEVICE__', $device_name, $widget_align_css);
				$css .= sprintf('@media(max-width: %1$spx){%2$s}', $device_value, str_replace('__DEVICE__', $device_name, $column_css));
			}

		    return lastudio_kit_helper()->minify_css($css);
        }

        public function register_custom_animation( $animations ){
            $new_animation = [
                'lakitShortFadeInDown' => 'Short Fade In Down',
                'lakitShortFadeInUp' => 'Short Fade In Up',
                'lakitShortFadeInLeft' => 'Short Fade In Left',
                'lakitShortFadeInRight' => 'Short Fade In Right',
                'lakitRevealCircle' => 'Reveal from circle',
                'lakitRevealTop' => 'Reveal from top',
                'lakitRevealBottom' => 'Reveal from bottom',
                'lakitRevealLeft' => 'Reveal from left',
                'lakitRevealRight' => 'Reveal from right',
                'lakitRevealCenter' => 'Reveal from center',
                'lakitFoldDown' => 'Fold Down',
            ];
            $animations['LaStudio Kit'] = $new_animation;
		    return $animations;
        }

        /**
         * Make remote request to mailchimp API
         *
         * @param  string $method API method to call.
         * @param  array  $args   API call arguments.
         * @return array|bool
         */
        private function api_call( $api_key, $list_id, $args = [] ) {

            $key_data = explode( '-', $api_key );

            if ( empty( $key_data ) || ! isset( $key_data[1] ) ) {
                return false;
            }

            $api_server = sprintf( 'https://%s.api.mailchimp.com/3.0/', $key_data[1] );

            $url = esc_url( trailingslashit( $api_server . 'lists/' . $list_id . '/members/' ) );

            $data = wp_json_encode( $args );

            $request_args = [
                'method'      => 'POST',
                'timeout'     => 20,
                'headers'     => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'apikey ' . $api_key
                ],
                'body'        => $data,
            ];

            $request = wp_remote_post( $url, $request_args );

            return wp_remote_retrieve_body( $request );
        }

        public function override_single_post_template( $value, $post_id, $meta_key, $single ){
            if ( '_wp_page_template' !== $meta_key || is_admin() || !is_singular() ) {
                return $value;
            }
            if( !empty($GLOBALS['post']) ){
                $_post = $GLOBALS['post'];
                if( $_post->ID && $_post->ID === $post_id ){
                    remove_filter( 'get_post_metadata', array( $this, 'override_single_post_template' ), 10 );
                    $current_template = get_post_meta( $post_id, '_wp_page_template', true );
                    if( !empty($current_template) && $current_template !== 'default' ){
                        return $current_template;
                    }
                    add_filter( 'get_post_metadata', array( $this, 'override_single_post_template' ), 10, 4 );
                    $_setting_key = 'single_' . $_post->post_type . '_template';
                    $global_post_template = lastudio_kit_settings()->get_option( $_setting_key, 'default' );
                    if( !empty($global_post_template) && $global_post_template !== 'default' ){
                        return $global_post_template;
                    }
                }
            }
            return $value;
        }

		/**
		 * @param LaStudio_Kit_Ajax_Manager $ajax_manager
		 */
		public function register_ajax_actions( $ajax_manager ){
			$ajax_manager->register_ajax_action( 'newsletter_subscribe', [ $this, 'ajax_newsletter_subscribe' ], false );
			$ajax_manager->register_ajax_action( 'elementor_template', [ $this, 'ajax_get_elementor_template' ], false);
			$ajax_manager->register_ajax_action( 'elementor_widget', [ $this, 'ajax_get_elementor_widget' ], false);
			$ajax_manager->register_ajax_action( 'login', [ $this, 'ajax_login_handle' ], true );
			$ajax_manager->register_ajax_action( 'register', [ $this, 'ajax_register_handle' ], true );
		}

		public function ajax_newsletter_subscribe( $request ){

			$api_key = apply_filters('lastudio-kit/mailchimp/api', lastudio_kit_settings()->get_option('mailchimp-api-key'));
			$list_id = apply_filters('lastudio-kit/mailchimp/list_id', lastudio_kit_settings()->get_option('mailchimp-list-id'));
			$double_opt = apply_filters('lastudio-kit/mailchimp/double_opt_in', lastudio_kit_settings()->get_option('mailchimp-double-opt-in'));

			$double_opt_in = filter_var( $double_opt, FILTER_VALIDATE_BOOLEAN );

			if ( ! $api_key ) {
				$return_data = [
					'type'      => 'error',
					'message' => $this->sys_messages['mailchimp']
				];
				return $return_data;
			}

			if ( isset( $request['use_target_list_id'] ) &&
			     filter_var( $request['use_target_list_id'], FILTER_VALIDATE_BOOLEAN ) &&
			     ! empty( $request['target_list_id'] )
			) {
				$list_id = esc_attr($request['target_list_id']);
			}

			if ( ! $list_id ) {
				$return_data = [
					'type'      => 'error',
					'message' => $this->sys_messages['mailchimp']
				];
				return $return_data;
			}

			$mail = $request['email'];

			if ( empty( $mail ) || ! is_email( $mail ) ) {
				$return_data = [
					'type'      => 'error',
					'message' => $this->sys_messages['invalid_mail']
				];
				return $return_data;
			}

			$args = [
				'email_address' => $mail,
				'status'        => $double_opt_in ? 'pending' : 'subscribed',
			];

			if ( ! empty( $data['additional'] ) ) {

				$additional = $data['additional'];

				foreach ( $additional as $key => $value ) {
					$merge_fields[ strtoupper( $key ) ] = $value;
				}

				$args['merge_fields'] = $merge_fields;

			}

			$response = $this->api_call( $api_key, $list_id, $args );

			if ( false === $response ) {
				$return_data = [
					'type'      => 'error',
					'message'   => $this->sys_messages['mailchimp']
				];
				return $return_data;
			}

			$response = json_decode( $response, true );

			if ( empty( $response ) ) {
				$return_data = [
					'type'      => 'error',
					'message' => $this->sys_messages['internal']
				];
				return $return_data;
			}

			if ( isset( $response['status'] ) && 'error' == $response['status'] ) {
				$return_data = [
					'type'      => 'error',
					'message' => esc_html( $response['error'] )
				];
				return $return_data;
			}
			$return_data = [
				'type'      => 'success',
				'message' => $this->sys_messages['subscribe_success']
			];

			return $return_data;
		}

		public function ajax_get_elementor_template( $request ){
			$helper = \LaStudioKit\Template_Helper::get_instance();

			$template_data = [
				'template_content' => '',
				'template_scripts' => [],
				'template_styles'  => [],
				'template_metadata' => []
			];
			$args = [
				'dev' => !empty($request['dev']) ? $request['dev'] : false
			];
			$template_ids = !empty($request['template_ids']) ? (array) $request['template_ids'] : [];
			if(empty($template_ids)){
				return [ $template_data ];
			}
			else{
				$returned_data = [];
				foreach ( $template_ids as $template_id ){
					$returned_data[$template_id] = $helper->callback( array_merge($args, ['id' => $template_id]), 'ajax' );
				}
				return $returned_data;
			}
		}

		public function ajax_get_elementor_widget( $request ){
			$helper = \LaStudioKit\Template_Helper::get_instance();
            $widget_args = $request['widget_args'] ?? [];
            $wc_filters = [];
            if(!empty($_GET)){
                foreach ($_GET as $key => $value){
                    $_tmp_key = strtolower($key);
                    if(str_contains($_tmp_key, 'filter_')){
                        $wc_filters[$_tmp_key] = $value;
                    }
                }
            }
            $is_dev = !empty($request['dev']) && LaStudio_Kit_Helper::string_to_bool($request['dev']);
            /**
             * disable cache if is wc filters
             */
            if(!empty($wc_filters)){
                $is_dev = true;
            }
			$args = [
				'template_id' => !empty($request['template_id']) ? absint($request['template_id']) : false,
				'widget_id' => !empty($request['widget_id']) ? $request['widget_id'] : false,
				'widget_args' => !empty($widget_args) ? $widget_args : false,
				'dev' => $is_dev
			];
			return $helper->widget_callback($args, 'ajax');
		}

		public function setup_post_per_page_manager( $query ){

			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}
            $posts_per_page_manager = lastudio_kit_settings()->get('posts_per_page_manager', []);
            $default_perpage    = !empty($posts_per_page_manager['is_blog']) ? $posts_per_page_manager['is_blog'] : false;
            $category_perpage   = !empty($posts_per_page_manager['is_category']) ? $posts_per_page_manager['is_category'] : $default_perpage;
            $tag_perpage        = !empty($posts_per_page_manager['is_tags']) ? $posts_per_page_manager['is_tags'] : $default_perpage;

            if(!empty($category_perpage)){
                $query->set( 'posts_per_page', $default_perpage );
            }
            if(!empty($category_perpage) && is_category()){
                $query->set( 'posts_per_page', $category_perpage );
            }
            if(!empty($tag_perpage) && is_tag()){
                $query->set( 'posts_per_page', $tag_perpage );
            }
            foreach ($posts_per_page_manager as $k => $value){

                if( !in_array($k, array( 'is_blog', 'is_category', 'is_tags' )) ){
                    $_type = explode('post_type__', $k);

                    if(!empty($_type[1]) && post_type_exists($_type[1]) && !empty($value)){
                        $post_type = $_type[1];
                        if ( is_post_type_archive( $post_type ) || (is_tax() && is_tax(get_object_taxonomies( $post_type ) ))) {
                            $query->set( 'posts_per_page', $value );
                        }
                    }
                }
            }
		}

        public function allowed_html(){
            $old_tag = wp_kses_allowed_html('post');
            $new_tag = [
                'meta' => [
                    'charset' => true,
                    'name' => true,
                    'content' => true,
                    'http-equiv' => true,
                ],
                'base' => [
                    'href' => true,
                    'target' => true,
                ],
                'link' => [
                    'href' => true,
                    'hreflang' => true,
                    'rel' => true,
                    'media' => true,
                    'crossorigin' => true,
                    'sizes' => true,
                    'title' => true,
                    'referrerpolicy' => true,
                    'type' => true,
                ],
                'style' => [
                    'media' => true,
                    'type' => true,
                    'data-no-optimize' => true,
                ],
                'script' => [
                    'async' => true,
                    'crossorigin' => true,
                    'defer' => true,
                    'integrity' => true,
                    'nomodule' => true,
                    'referrerpolicy' => true,
                    'src' => true,
                    'type' => true,
                    'data-no-optimize' => true,
                ],
                'iframe' => [
                    'allow' => true,
                    'allowfullscreen' => true,
                    'allowpaymentrequest' => true,
                    'height' => true,
                    'loading' => true,
                    'name' => true,
                    'referrerpolicy' => true,
                    'sandbox' => true,
                    'src' => true,
                    'srcdoc' => true,
                    'width' => true,
                ],
                'noscript' => true
            ];
            return array_merge($old_tag, $new_tag);
        }

        public function custom_head_code(){
            $custom_css = lastudio_kit_settings()->get('custom_css', '');
            $head_code  = lastudio_kit_settings()->get('head_code', '');
            if(!empty($custom_css)){
                echo sprintf('<style>%1$s</style>', wp_strip_all_tags($custom_css)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            if(!empty($head_code)){
                echo wp_kses($head_code, $this->allowed_html());
            }
        }

        public function custom_footer_code(){
            $footer_code = lastudio_kit_settings()->get('footer_code', '');
            if(!empty($footer_code)){
                echo wp_kses($footer_code, $this->allowed_html());
            }
        }

		public function is_active_recaptchav3(){
			$recaptchav3 = lastudio_kit_settings()->get('recaptchav3');
			return !empty($recaptchav3['site_key']) && !empty($recaptchav3['secret_key']);
		}

		public function get_recaptchav3_site_key(){
			$recaptchav3 = lastudio_kit_settings()->get('recaptchav3');
			return esc_attr($recaptchav3['site_key'] ?? '');
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 */
		public function verify_recaptchav3( $token ){
			$is_human = false;
			if( !$this->is_active_recaptchav3() ){
				return !$is_human;
			}
			if(empty($token)){
				return $is_human;
			}
			$recaptchav3 = lastudio_kit_settings()->get('recaptchav3');
			$secret = $recaptchav3['secret_key'] ?? '';
			$endpoint = 'https://www.google.com/recaptcha/api/siteverify';
			$request = array(
				'body' => array(
					'secret' => $secret,
					'response' => $token,
				),
			);
			$response = wp_remote_post( esc_url_raw( $endpoint ), $request );
			if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
				return $is_human;
			}
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body, true );
			$score = $response_body['score'] ?? 0;
			$is_human = $score > 0.50;
			return $is_human;
		}

		/**
		 * @param array $request
		 *
		 * @return array
		 */
		public function ajax_login_handle( $request ){

			$log = $request['log'] ?? $request['username'] ?? '';
			$pwd = $request['pwd'] ?? $request['password'] ?? '';
			$rememberme = $request['rememberme'] ?? '';
			$is_human = true;
			if(isset($request['lakit_recaptcha_response'])){
				$is_human = $this->verify_recaptchav3( $request['lakit_recaptcha_response'] ?? '' );
			}
			if(!$is_human){
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> reCAPTCHA response token is invalid.', 'lastudio-kit' ),
				];
			}
			if(empty( $log )){
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> The username field is empty.' )
				];
			}

			if(empty( $pwd )){
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> The password field is empty.' )
				];
			}

			$signon = wp_signon([
				'user_login'    => $log ?? '',
				'user_password' => $pwd ?? '',
				'remember'      => $rememberme ?? '',
			]);

			if(is_wp_error($signon)){
				return [
					'type'      => 'error',
					'message'   => $signon->get_error_message()
				];
			}
			else{
				return [
					'type'          => 'success',
					'message'       => __('Logged in successful', 'lastudio-kit'),
					'redirect_to'   => esc_url($request['redirect_to'] ?? $request['redirect'] ?? home_url('/') )
				];
			}
		}

		/**
		 * @param array $request
		 *
		 * @return array
		 */
		public function ajax_register_handle( $request ){

			$is_human = $this->verify_recaptchav3( $request['lakit_recaptcha_response'] ?? '' );
			$has_username = $request['lakit_field_log'] ?? 'no';
			$has_pwd = $request['lakit_field_pwd'] ?? 'no';
			$has_cpwd = $request['lakit_field_cpwd'] ?? 'no';

			$username = $request['username'] ?? '';
			$email = $request['email'] ?? '';
			$password = $request['password'] ?? '';
			$cpassword = $request['password-confirm'] ?? '';

			$username   = wp_slash($username);
			$email      = wp_slash($email);

			if('yes' !== get_option( 'woocommerce_enable_myaccount_registration' ) && 'yes' !== get_option( 'users_can_register' )){
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> This feature is not available.', 'lastudio-kit' ),
				];
			}

			if(!$is_human){
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> reCAPTCHA response token is invalid.', 'lastudio-kit' ),
				];
			}
			if($has_username == 'yes'){
				if( empty($username) || !validate_username($username) ) {
					return [
						'type'      => 'error',
						'message'   => __( '<strong>Error:</strong> Please enter a username.' )
					];
				}
				if ( username_exists( $username ) ) {
					return [
						'type'      => 'error',
						'message'   => __( '<strong>Error:</strong> This username is already registered. Please choose another one.' )
					];
				}
			}
			// Check the email address.
			if ( empty( $email ) || ! is_email( $email ) ) {
				return [
					'type'      => 'error',
					'message'   => __( '<strong>Error:</strong> Please enter an email address.' )
				];
			}
			if ( email_exists( $email ) ) {
				return [
					'type'      => 'error',
					'message'   =>  __( '<strong>Error:</strong> This email is already registered. Please choose another one.' )
				];
			}
			$password_generated = false;
			if($has_pwd == 'yes'){
				if(empty($password) || mb_strlen($password) < 6){
					return [
						'type'      => 'error',
						'message'   => __( '<strong>Error:</strong> Please enter a password.' )
					];
				}
				if($has_cpwd == 'yes' && $cpassword !== $password){
					return [
						'type'      => 'error',
						'message'   => __( '<strong>Error:</strong> Passwords do not match. Please enter the same password in both password fields.' )
					];
				}
			}
			else{
				$password = wp_generate_password();
				$password_generated = true;
			}
			if($has_username != 'yes'){
				$username = self::generate_username($email);
			}
			$posted_user_data = [
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $email,
			];
			if(function_exists('WC')){
				$posted_user_data['role'] = 'customer';
			}
			$new_customer_id = wp_insert_user($posted_user_data);
			if(is_wp_error($new_customer_id)){
				return [
					'type'      => 'error',
					'message'   => $new_customer_id->get_error_message()
				];
			}
			if(!function_exists('WC')){
				wp_new_user_notification($new_customer_id, null, 'user');
			}
			else{
				do_action( 'woocommerce_created_customer', $new_customer_id, $posted_user_data, $password_generated );
			}
			$return_data = [
				'type'      => 'success',
				'message'   => __('Your account was created successfully. Your login details have been sent to your email address.', 'lastudio-kit')
			];
			return $return_data;
		}

		public function set_post_views_count(){
			if(is_singular()){
				$obj_id = get_queried_object_id();
				$counter = get_post_meta( $obj_id, 'post_views_count', true );
				if(!is_numeric($counter)){
					$counter = 0;
				}
				$counter = $counter + 1;
				update_post_meta($obj_id, 'post_views_count', $counter);
			}
		}

        public function e_widget_blacklist( $list ){
            $available_extension = lastudio_kit_settings()->get_option('avaliable_extensions', []);
            $is_active = !empty($available_extension['disable_wp_default_widgets']) && filter_var($available_extension['disable_wp_default_widgets'], FILTER_VALIDATE_BOOLEAN);
            if($is_active){
                global $wp_widget_factory;
                $list = array_merge($list, array_keys($wp_widget_factory->widgets));
            }
            return $list;
        }
        public function e_pro_widgets( $settings ){
            $available_extension = lastudio_kit_settings()->get_option('avaliable_extensions', []);
            $is_active = !empty($available_extension['disable_wp_default_widgets']) && filter_var($available_extension['disable_wp_default_widgets'], FILTER_VALIDATE_BOOLEAN);
            if($is_active && isset($settings['promotionWidgets'])){
                unset($settings['promotionWidgets']);
            }
            return $settings;
        }

        public function e_shapes( $additional_shapes ){
	        $lakit_shapes = [];
	        $title =  esc_html_x( 'LA-Kit Shape', 'Shapes', 'lastudio-kit' );
			for ($i = 1; $i <= 19; $i++){
				$k = $i < 10 ? '0' . $i : $i;
				$lakit_shapes['lakit-shape-' . $k] = [
					'title' => $title . ' ' . $k,
					'has_negative' => true,
					'has_flip' => true,
					'url'   => lastudio_kit()->plugin_url('assets/shapes/custom-'.$k.'.svg'),
					'path'   => lastudio_kit()->plugin_path('assets/shapes/custom-'.$k.'.svg'),
				];
			}
            return array_merge( $additional_shapes, $lakit_shapes );
        }

        public function enqueue_frontend_scripts(){
            if(!!lastudio_kit()->elementor()->assets_loader){
                $this->register_assets();
            }
        }
        private function register_assets() {
            lastudio_kit()->elementor()->assets_loader->add_assets( $this->get_assets() );
        }

        private function get_assets() {

            $new_animation = [
                'lakitShortFadeInDown' => 'Short Fade In Down',
                'lakitShortFadeInUp' => 'Short Fade In Up',
                'lakitShortFadeInLeft' => 'Short Fade In Left',
                'lakitShortFadeInRight' => 'Short Fade In Right',
                'lakitRevealCircle' => 'Reveal from circle',
                'lakitRevealTop' => 'Reveal from top',
                'lakitRevealBottom' => 'Reveal from bottom',
                'lakitRevealLeft' => 'Reveal from left',
                'lakitRevealRight' => 'Reveal from right',
                'lakitRevealCenter' => 'Reveal from center',
                'lakitFoldDown' => 'Fold Down',
            ];

            $styles = [];
            foreach ($new_animation as $k => $v){
                $styles['e-animation-' . $k] = [
                    'src' => lastudio_kit()->plugin_url('assets/css/animations/'.$k.'.min.css'),
                    'version' => lastudio_kit()->get_version(true),
                    'dependencies' => []
                ];
            }

            return [
                'styles' => $styles
            ];
        }

        public function woocommerce_add_to_cart_redirect( $url ){
            if( isset( $_REQUEST['lakit_buynow'] ) ){
                $url = wc_get_checkout_url();
            }
            return $url;
        }

        public function add_body_class( $classes )
        {
            if( Elementor\Plugin::instance()->preview->is_preview_mode() ){
                $classes[] = 'lakit-is-elementor-editor-mode';
            }
            return $classes;
        }
	}

}

/**
 * Returns instance of LaStudio_Kit_Integration
 *
 * @return object|null
 */
function lastudio_kit_integration() {
	return LaStudio_Kit_Integration::get_instance();
}
