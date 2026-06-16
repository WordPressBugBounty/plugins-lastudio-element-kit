<?php
/**
 * Vue.js based Interface Builder module
 *
 * Version: 1.3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'CX_Vue_UI' ) ) {

	/**
	 * Class Cherry Interface Builder.
	 *
	 * @since 1.0.0
	 */
	class CX_Vue_UI {

		/**
		 * Module directory path.
		 *
		 * @since 1.0.0
		 * @access protected
		 * @var string.
		 */
		protected $path;

		/**
		 * Module directory URL.
		 *
		 * @since 1.0.0
		 * @access protected
		 * @var string.
		 */
		protected $url;

		/**
		 * Module version
		 *
		 * @var string
		 */
		protected $version = '1.3.0';

		/**
		 * [$assets_enqueued description]
		 * @var boolean
		 */
		protected $assets_enqueued = false;

		/**
		 * CX_Vue_UI constructor.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct( array $args = array() ) {

			$this->path = ! empty( $args['path'] ) ? $args['path'] : false;
			$this->url  = ! empty( $args['url'] ) ? $args['url'] : false;

			if ( ! $this->path || ! $this->url ) {
				wp_die(
					'CX_Vue_UI not initialized. Module URL and Path should be passed into constructor',
					'CX_Vue_UI Error'
				);
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		}

		/**
		 * Enqueue builder assets
		 *
		 * @return void
		 */
		public function enqueue_assets() {

			if ( $this->assets_enqueued ) {
				return;
			}

			wp_enqueue_media();

			$suffix = '.min';

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$suffix = '';
			}

			wp_register_script(
				'cx-vue',
				$this->url . 'assets/js/vue' . $suffix . '.js',
				array(),
				'2.6.10',
                [ 'in_footer' => true ]
			);

			wp_enqueue_script(
				'cx-vue-ui',
				$this->url . 'assets/js/cx-vue-ui.js',
				array( 'cx-vue' ),
				$this->version,
                [ 'in_footer' => true ]
			);

			add_action( 'admin_footer', array( $this, 'print_templates' ), 0 );

			$this->assets_enqueued = true;

		}

		/**
		 * Returns registered components list
		 *
		 * @return array
		 */
		public function components_list() {
            $basics = [
                // Layout elements
                'title',
                'collapse',
                'component-wrapper',
                'button',
                'repeater',
                'repeater-item',
                'popup',
                'list-table',
                'list-table-heading',
                'list-table-item',
                'tabs',
                'tabs-panel',
                'pagination',
                'notice',

                // Form elements
                'input',
                'time',
                'textarea',
                'switcher',
                'iconpicker',
                'select',
                'f-select',
                'checkbox',
                'radio',
                'colorpicker',
                'wp-media',
                'dimensions',
            ];
			return apply_filters( 'cx-vue-ui/components-list', [] );
		}

		/**
		 * Print components templates
		 *
		 * @return void
		 */
		public function print_templates() {

            $core_templates = $this->path . 'dist/templates.php';
            if ( is_readable( $core_templates ) ) {
                require $core_templates;
            }
            $dynamic_templates = $this->get_dynamic_templates();
            if(!empty($dynamic_templates)){
                foreach ( $dynamic_templates as $slug => $template ) {
                    printf(
                        '<script type="text/x-template" id="%1$s">%2$s</script>',
                        esc_attr( 'cx-vui-' . $slug ),
                        $template
                    );
                }
            }
		}

        protected function get_dynamic_templates() {
            $dynamic_lists = $this->components_list();
            if(empty($dynamic_lists)){
                return [];
            }
            $cache_key = sprintf( 'cx_vui_%s_%s',
                md5( $this->path ),
                filemtime( $this->path . 'components/' )
            );
            $templates = wp_cache_get( $cache_key, 'lakit_cx_vui' );
            if ( false !== $templates ) {
                return $templates;
            }
            $templates = array();
            foreach ( $dynamic_lists as $slug ) {
                $file = $this->path . 'components/' . $slug . '.php';
                if ( ! is_readable( $file ) ) {
                    continue;
                }
                ob_start();
                include $file;
                $templates[ $slug ] = ob_get_clean();
            }
            wp_cache_set( $cache_key, $templates, 'lakit_cx_vui', HOUR_IN_SECONDS );
            return $templates;
        }
	}

}
