<?php
namespace LaStudioKit;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define Posts class
 */
class Template_Helper {

    /**
     * [$depended_scripts description]
     * @var array
     */
    public $depended_scripts = [];

    /**
     * [$depended_styles description]
     * @var array
     */
    public $depended_styles = [];

    public $document_data = [];

    /**
     * Instance of this class.
     *
     * @since   1.0.0
     * @access  private
     * @var     Template_Helper
     */
    private static $instance;

    /**
     * Provides access to a single instance of a module using the singleton pattern.
     *
     * @since   1.0.0
     * @return	object
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Here initialize our namespace and resource name.
    public function __construct() {
        add_action( 'wp_update_nav_menu', array( $this, 'clear_transient_for_menu' ) );
        add_action( 'wp_delete_nav_menu', array( $this, 'clear_transient_for_menu' ) );
        add_action( 'post_updated', array( $this, 'clear_transient_for_post' ) );
        add_action( 'deleted_post', array( $this, 'clear_transient_for_post' ) );
        add_action( 'elementor/core/files/clear_cache', array( $this, 'clear_all_transient' ) );
    }

    public function callback( $args, $type = 'rest' ) {

        $default = [
            'template_content' => '',
            'template_scripts' => [],
            'template_styles'  => [],
            'template_metadata' => []
        ];

        $template_id = ! empty( $args['id'] ) ? $args['id'] : false;
        $dynamic_id = ! empty( $args['dynamic_id'] ) ? $args['dynamic_id'] : 0;

        $is_dev = $args['dev'] ?? false;

        $dev = (bool)filter_var($is_dev, FILTER_VALIDATE_BOOLEAN);

        if ( ! $template_id || !defined('ELEMENTOR_VERSION' ) ) {
            $template_data = $default;
            return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
        }

	    $rest_allow_types = apply_filters('lastudio-kit/rest/elmenetor/allow_types', ['elementor_library', 'nav_menu_item']);

	    // Resolve to the real post before anything else: "228", "0228" and "228abc" all load
	    // post 228, so using the raw value in the cache key below would mint a separate cache
	    // entry for every spelling of the same ID.
	    $template_post = get_post( absint( $template_id ) );

        if( ! $template_post || !in_array($template_post->post_type, $rest_allow_types, true) || $template_post->post_status !== 'publish' || post_password_required($template_post) ){
	        $template_data = $default;
	        return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
        }

	    $template_id = $template_post->ID;

		if(!empty($dynamic_id)){
			$dynamic_post = get_post( absint( $dynamic_id ) );
			if( ! self::is_post_readable( $dynamic_post ) ){
				return $type == 'rest' ? rest_ensure_response( $default ) : $default;
			}
			$dynamic_id = $dynamic_post->ID;
			global $post;
			$post = $dynamic_post;
		}

        $transient_key = md5( sprintf( 'lakit_doc_%1$s_dynamic_%2$s', $template_id, $dynamic_id ) );

        $template_data = get_transient( $transient_key );

        if ( !empty( $template_data ) && !$dev ) {
            if(!is_user_logged_in()){
                $template_data['template_metadata'] = [];
            }
            return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
        }

        // Cached responses are cheap; only throttle requests that trigger a real render.
        if($type !== 'rest'){
            $passed_rate_limit = self::apply_rate_limit();
            if( is_wp_error( $passed_rate_limit ) ){
                return $passed_rate_limit;
            }
        }

        $plugin = lastudio_kit()->elementor();

        add_filter('paginate_links', [ $this, 'paginate_links' ], 10, 1);

        $template_scripts = [];
        $template_styles = [];

        $fonts_link = $this->get_elementor_template_fonts_url( $template_id );

        if ( $fonts_link ) {
            $template_styles[ 'lastudio-kit-google-fonts-css-' . $template_id ] = $fonts_link;
        }

        $plugin->frontend->register_scripts();
        $plugin->frontend->register_styles();

        $content = $plugin->frontend->get_builder_content_for_display( $template_id, true );

        $this->get_elementor_template_scripts( $template_id );
        $this->get_elementor_template_icon_fonts_url( $template_id );

        $script_depends = array_unique( $this->depended_scripts );

        $style_depends = array_unique( $this->depended_styles );

        foreach ( $script_depends as $script ) {
	        $this->get_script_uri_by_handler( $script, $template_scripts );
        }

        foreach ( $style_depends as $style ) {
            if( $tag = $this->get_style_uri_by_handler( $style ) ){
                $template_styles[ $style ] = $tag;
            }
        }

        $template_metadata = [];

        if(isset($this->document_data[$template_id])){
            $template_metadata = $this->document_data[$template_id];
        }

        $template_data = [
            'template_content' => $content,
            'template_scripts' => $template_scripts,
            'template_styles'  => $template_styles,
            'template_metadata' => $template_metadata
        ];

        set_transient( $transient_key, $template_data, 24 * HOUR_IN_SECONDS );
        self::set_transient_key('post_type', $template_id, $transient_key );
		if(!empty($dynamic_id)){
			self::set_transient_key('post_type', $dynamic_id, $transient_key );
		}

        if(!is_user_logged_in()){
            $template_data['template_metadata'] = [];
        }
        return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
    }

    /**
     * @return [type] [description]
     */
    public function get_elementor_template_fonts_url( $template_id ) {

        $post_css = new \Elementor\Core\Files\CSS\Post( $template_id );

        $post_meta = $post_css->get_meta();

        if ( ! isset( $post_meta['fonts'] ) ) {
            return false;
        }

        $google_fonts = $post_meta['fonts'];

        $google_fonts = array_unique( $google_fonts );

        if ( empty( $google_fonts ) ) {
            return false;
        }

        foreach ( $google_fonts as $k => &$font ) {
            $font_type = \Elementor\Fonts::get_font_type( $font );
            if($font_type == \Elementor\Fonts::GOOGLE || $font_type == \Elementor\Fonts::EARLYACCESS){
                $font = str_replace( ' ', '+', $font ) . ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
            }
            else{
                unset($google_fonts[$k]);
            }
        }

        if(empty($google_fonts)){
            return false;
        }

        $fonts_url = sprintf( 'https://fonts.googleapis.com/css?family=%s', implode( rawurlencode( '|' ), $google_fonts ) );

        return $fonts_url;

    }

    public function get_elementor_template_icon_fonts_url( $template_id ){

	    $post_css = new \Elementor\Core\Files\CSS\Post( $template_id );

	    $post_meta = $post_css->get_meta();

	    if ( ! isset( $post_meta['icons'] ) ) {
		    return false;
	    }

	    $icon_fonts = $post_meta['icons'];

	    $icon_fonts = array_unique( $icon_fonts );

	    foreach ($icon_fonts as $icon_font){
	    	if(!empty($icon_font)){
	    		$handler = 'elementor-icons-' . $icon_font;
	    		$this->get_style_deps_by_handler($handler);
		    }
	    }
    }

    /**
     * [get_elementor_template_scripts_url description]
     * @param  [type] $template_id [description]
     * @return [type]              [description]
     */
    public function get_elementor_template_scripts( $template_id ) {

        $document = lastudio_kit()->elementor()->documents->get( $template_id );

        if(!$document){
        	return;
        }

        $main_post = $document->get_main_post();

        $document_data = [
            'title'     => $main_post->post_title,
            'sub_title' => $main_post->post_type == 'nav_menu_item' ? 'Menu Item' : $document::get_title(),
            'href'      => $document->get_edit_url(),
            'id'        => $template_id
        ];

        $this->document_data[$template_id] = $document_data;

        $elements_data = $document->get_elements_raw_data();

        $this->find_widgets_script_handlers( $elements_data );

    }

    /**
     * [find_widgets_script_handlers description]
     * @param  [type] $elements_data [description]
     * @return [type]                [description]
     */
    public function find_widgets_script_handlers( $elements_data ) {

        foreach ( $elements_data as $element_data ) {

            if ( 'widget' === $element_data['elType'] ) {
                $widget = lastudio_kit()->elementor()->elements_manager->create_element_instance( $element_data );

                $widget_script_depends = $widget->get_script_depends();

                if ( ! empty( $widget_script_depends ) ) {
                    foreach ( $widget_script_depends as $key => $script_handler ) {
                        $this->depended_scripts[] = $script_handler;
                    }
                }

                $widget_style_depends = $widget->get_style_depends();
                if ( ! empty( $widget_style_depends ) ) {
                    foreach ( $widget_style_depends as $key => $style_handler ) {
                        $this->depended_styles[] = $style_handler;
                    }
                }

            }
            else {
                $element = lastudio_kit()->elementor()->elements_manager->create_element_instance( $element_data );

                $childrens = $element->get_children();

                foreach ( $childrens as $key => $children ) {
                    $children_data[$key] = $children->get_raw_data();

                    $this->find_widgets_script_handlers( $children_data );
                }
            }
        }
    }

    /**
     * [get_script_uri_by_handler description]
     * @param  string $handler [description]
     * @param  array $template_scripts [description]
     * @param  boolean $inDeep [description]
     * @return [type]          [description]
     */
    public function get_script_uri_by_handler( $handler, &$template_scripts, $inDeep = true ) {
        global $wp_scripts;

        if ( isset( $wp_scripts->registered[ $handler ] ) ) {

        	if( !empty($wp_scripts->registered[$handler]->deps) ){
        		foreach ( $wp_scripts->registered[$handler]->deps as $dep ) {
			        if($inDeep){
				        $this->get_script_uri_by_handler( $dep, $template_scripts, false );
			        }
		        }
	        }

            $src = $wp_scripts->registered[ $handler ]->src;

            if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $wp_scripts->content_url && 0 === strpos( $src, $wp_scripts->content_url ) ) ) {
                $src = $wp_scripts->base_url . $src;
            }

	        $template_scripts[ $handler ] = $src;
        }
    }

    /**
     * [get_style_uri_by_handler description]
     * @param  [type] $handler [description]
     * @return [type]          [description]
     */
    public function get_style_uri_by_handler( $handler ) {
        global $wp_styles;

        if ( isset( $wp_styles->registered[ $handler ] ) ) {

            $src = $wp_styles->registered[ $handler ]->src;

            if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $wp_styles->content_url && 0 === strpos( $src, $wp_styles->content_url ) ) ) {
                $src = $wp_styles->base_url . $src;
            }

            return $src;
        }

        return false;
    }

    public function get_style_deps_by_handler( $handler ){
	    global $wp_styles;
	    if ( isset( $wp_styles->registered[ $handler ] ) ) {
	    	$deps = $wp_styles->registered[ $handler ]->deps;
	    	if(!empty($deps)){
	    		foreach ($deps as $dep){
				    $this->depended_styles[] = $dep;
			    }
		    }
	    	$this->depended_styles[] = $handler;
	    }
    }


    /**
     * Hard cap on how many cache keys are tracked per type in the key-map option.
     *
     * Without a cap the map grows for every distinct cache key ever created and, because
     * it used to be autoloaded, its whole contents were unserialized on every request.
     */
    const TRANSIENT_KEY_LIMIT = 500;

    public static function set_transient_key( $type, $id, $key ){

        $transient_manager = get_option('LaStudioKitAPI_transient_manager', []);

        if(!is_array($transient_manager)){
            $transient_manager = [];
        }

        if(!isset($transient_manager[$type]) || !is_array($transient_manager[$type])){
            $transient_manager[$type] = [];
        }

        // Already tracked for this object: nothing to write.
        if(isset($transient_manager[$type][$key]) && $transient_manager[$type][$key] === $id){
            return;
        }

        $transient_manager[$type][$key] = $id;

        $limit = (int) apply_filters('lastudio-kit/transient-manager/key-limit', self::TRANSIENT_KEY_LIMIT, $type);

        if($limit > 0 && count($transient_manager[$type]) > $limit){
            // Keys are inserted in creation order, so the oldest ones come first.
            $overflow = count($transient_manager[$type]) - $limit;
            $old_keys = array_slice(array_keys($transient_manager[$type]), 0, $overflow);
            foreach ($old_keys as $old_key){
                if($old_key === $key){
                    continue;
                }
                delete_transient($old_key);
                unset($transient_manager[$type][$old_key]);
            }
        }

        // autoload = false: this map is only read by the cache-clearing routines, never
        // on a normal page load.
        update_option('LaStudioKitAPI_transient_manager', $transient_manager, false);
    }

    public function clear_transient_for_menu($menu_id){
        $all_transient = get_option('LaStudioKitAPI_transient_manager', []);
        $need_update = false;
        if(!empty($all_transient['menu'])){
            foreach ($all_transient['menu'] as $k => $id){
                if($id == $menu_id){
                    $need_update = true;
                    delete_transient($k);
                    unset($all_transient['menu'][$k]);
                }
            }
        }
        if($need_update){
            update_option('LaStudioKitAPI_transient_manager', $all_transient, false);
        }
    }

    public function clear_transient_for_post($post_id){

        $all_transient = get_option('LaStudioKitAPI_transient_manager', []);
        $need_update = false;
        if(!empty($all_transient['post_type'])){
            foreach ($all_transient['post_type'] as $k => $id){
                if($id == $post_id){
                    $need_update = true;
                    delete_transient($k);
                    unset($all_transient['post_type'][$k]);
                }
            }
        }
        if($need_update){
            update_option('LaStudioKitAPI_transient_manager', $all_transient, false);
        }
    }

    public function clear_all_transient(){
        $all_transient = get_option('LaStudioKitAPI_transient_manager', []);
        if(!empty($all_transient)){
            foreach ($all_transient as $type => $item){
                foreach ($item as $k => $id){
                    delete_transient($k);
                }
            }
        }
        update_option('LaStudioKitAPI_transient_manager', [], false);
    }

    /**
     * Whether the current visitor may read the given post (public post type + publicly viewable, or read_post capability).
     *
     * @param \WP_Post|null $post
     * @return bool
     */
    public static function is_post_readable( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            return false;
        }
        if ( post_password_required( $post ) ) {
            return false;
        }
        if ( is_post_type_viewable( $post->post_type ) && is_post_publicly_viewable( $post ) ) {
            return true;
        }
        return is_user_logged_in() && current_user_can( 'read_post', $post->ID );
    }

    public function render_elementor_template_via_ajax( $request_args, $error ){
        return $this->callback($request_args, 'ajax');
    }

    public function paginate_links( $link ){

        $custom_paged_key = isset($_REQUEST['lakitpagedkey']) ? $_REQUEST['lakitpagedkey'] : false;

        $link = remove_query_arg(['_', 'lakitpagedkey', 'lakit-ajax', '_nonce', 'actions'], $link);
        $old_link = $link;

        return $old_link;

        $curl = add_query_arg( null, null, false );
        $curl = remove_query_arg(['_', 'lakitpagedkey', 'lakit-ajax', '_nonce', 'actions'], $curl);

        if(!empty($custom_paged_key)){
            $curl = remove_query_arg($custom_paged_key, $curl);
        }
        $curl = esc_url_raw( $curl );


        $link = str_replace($curl, '', $link);
        $link = str_replace('&', '?', $link);

        return $link;
    }

    public function widget_callback( $args, $type = 'rest' ) {

        $default_response = [ 'template_content' => '' ];

        $template_id = ! empty( $args['template_id'] ) ? $args['template_id'] : false;
        $widget_id = ! empty( $args['widget_id'] ) ? $args['widget_id'] : false;
        $is_dev = isset($args['dev']) ? filter_var( $args['dev'], FILTER_VALIDATE_BOOLEAN ) : false;
	    $widget_args = ! empty( $args['widget_args'] ) ? $args['widget_args'] : false;

        $cache_timeout = 15 * DAY_IN_SECONDS;

        $custom_paged_key = $_REQUEST['lakitpagedkey'] ?? false;

        // Accept only a plain parameter name, and keep its original spelling so the lookup
        // below still matches what the front end sent.
        if( ! is_string( $custom_paged_key ) || ! preg_match( '/^[A-Za-z0-9_\-]{1,64}$/', $custom_paged_key ) ){
            $custom_paged_key = false;
        }

        // The client picks both the parameter name and its value, so the value is clamped to
        // a plain integer in a sane range: it is part of the cache key and must not be able
        // to mint an unbounded number of distinct keys.
        $paged_key = 0;
        if(!empty($custom_paged_key) && isset($_REQUEST[$custom_paged_key]) && is_scalar($_REQUEST[$custom_paged_key])){
            $max_paged = (int) apply_filters('lastudio-kit/rest/max-paged', 500);
            $paged_key = min( absint( $_REQUEST[$custom_paged_key] ), $max_paged );
        }

        if(!empty($custom_paged_key) && !empty($paged_key)){
            $cache_timeout = 5 * MINUTE_IN_SECONDS;
        }

        if ( (empty($template_id) && empty($widget_id)) || !defined('ELEMENTOR_VERSION' ) ) {
	        return $type == 'rest' ? rest_ensure_response( $default_response ) : $default_response;
        }

	    $widget_allow_types = apply_filters('lastudio-kit/rest/elmenetor/allow_types', ['elementor_library', 'nav_menu_item']);
	    $template_post = get_post( absint( $template_id ) );
	    if( ! $template_post || ( ! in_array( $template_post->post_type, $widget_allow_types, true ) && ! self::is_post_readable( $template_post ) ) || 'publish' !== $template_post->post_status || post_password_required( $template_post ) ){
		    return $type == 'rest' ? rest_ensure_response( $default_response ) : $default_response;
	    }
	    $template_id = $template_post->ID;

	    $cache_name = sprintf( 'lakit_doc_%1$s_widget_%2$s_paged_%3$s', $template_id, $widget_id, $paged_key );
		if(!empty($widget_args)){
			$cache_name = sprintf( 'lakit_doc_%1$s_widget_%2$s_paged_%3$s_args_%4$s', $template_id, $widget_id, $paged_key, wp_json_encode($widget_args) );
		}

        $transient_key = md5( $cache_name );

        $template_data = get_transient( $transient_key );

        if ( !empty( $template_data ) && !$is_dev ) {
	        return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
        }

        // Cached responses are cheap; only throttle requests that trigger a real render.
        if($type !== 'rest'){
            $passed_rate_limit = self::apply_rate_limit();
            if( is_wp_error( $passed_rate_limit ) ){
                return $passed_rate_limit;
            }
        }

        $doc_meta = get_post_meta( $template_id, '_elementor_data', true );

        if(empty($doc_meta)){
	        return $type == 'rest' ? rest_ensure_response( $default_response ) : $default_response;
        }

        $doc_meta = @json_decode($doc_meta, true);

        if(empty($doc_meta)){
	        return $type == 'rest' ? rest_ensure_response( $default_response ) : $default_response;
        }

		$widget_data = $this->find_widget_data($doc_meta, $widget_id);

        if(empty($widget_data) || ( !empty($widget_data) && !is_array($widget_data) )){
	        return $type == 'rest' ? rest_ensure_response( $default_response ) : $default_response;
        }

	    if(!empty($widget_args['filter'])){
		    $widget_settings = $widget_data['settings'];
			$query_include = !empty($widget_settings['query_include']) ? $widget_settings['query_include'] : [];
		    $query_include[] = 'terms';
		    $query_include = array_unique($query_include);

			$query_include_ids = !empty($widget_settings['query_include_term_ids']) ? $widget_settings['query_include_term_ids'] : [];
			$filter_id = absint($widget_args['filter']);
			if($filter_id > 0){
				$query_include_ids[] = absint($widget_args['filter']);
				$query_include_ids = array_unique($query_include_ids);
				$widget_settings['query_include'] = $query_include;
				$widget_settings['query_include_term_ids'] = $query_include_ids;
				$widget_data['settings'] = $widget_settings;
			}
	    }

        ob_start();
        add_filter('paginate_links', [ $this, 'paginate_links' ], 10, 1);

        $widget = lastudio_kit()->elementor()->elements_manager->create_element_instance( $widget_data );
        if ( $widget ) {
            $widget->print_element();
        }
        $content = ob_get_clean();

        $template_data = [
            'template_content' => $content,
        ];

        if(!empty($content)){
            set_transient( $transient_key, $template_data, $cache_timeout );
            self::set_transient_key('post_type', $template_id, $transient_key );
        }

	    return $type == 'rest' ? rest_ensure_response( $template_data ) : $template_data;
    }

    public static function find_widget_data( $data, $widget_id ){
        $return = [];
        foreach ( $data as $item ){
            if( 'widget' === $item['elType'] && $item['id'] == $widget_id){
                $return = $item;
                break;
            }
            else{
                $return = self::find_widget_data($item['elements'], $widget_id);
                if(!empty($return)){
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * @param string $key_prefix
     * @return true|\WP_Error
     */
    /**
     * Visitor address the rate limiter counts against.
     *
     * REMOTE_ADDR by default. Behind a reverse proxy or a CDN that address is the proxy's,
     * so every visitor shares one bucket and a single visitor can rate-limit the whole site.
     * A site that really sits behind one can name the header carrying the visitor address:
     *
     *     define( 'LAKIT_TRUSTED_PROXY_HEADER', 'HTTP_CF_CONNECTING_IP' );
     *
     * Set it ONLY where the proxy always overwrites that header on the way in. On a site
     * reachable directly, any visitor can forge the header and walk around the limit.
     *
     * @return string Validated IP address, or '' when there is none (CLI, cron).
     */
    public static function get_client_ip(){

        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

        $header = defined('LAKIT_TRUSTED_PROXY_HEADER') ? (string) LAKIT_TRUSTED_PROXY_HEADER : '';
        $header = (string) apply_filters('lastudio-kit/rate-limit/trusted-proxy-header', $header);

        if ( '' !== $header && ! empty( $_SERVER[ $header ] ) ) {
            // X-Forwarded-For style headers hold a list; the left-most entry is the client.
            $forwarded = explode( ',', (string) $_SERVER[ $header ] );
            $candidate = trim( $forwarded[0] );
            if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
                $ip = $candidate;
            }
        }

        $ip = (string) apply_filters('lastudio-kit/rate-limit/client-ip', $ip);

        return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
    }

    /**
     * @param string $key_prefix    Bucket the request is counted against.
     * @param int    $default_limit Requests allowed per window before the filter runs.
     * @return true|\WP_Error
     */
    public static function apply_rate_limit( $key_prefix = 'lakit_rl_rest_', $default_limit = 30 ){

        $user_ip = self::get_client_ip();

        // Nothing to attribute the request to (CLI, cron): there is no caller to throttle.
        if ( '' === $user_ip ) {
            return true;
        }

        $limit = (int) apply_filters('lastudio-kit/rate-limit/limit', (int) $default_limit, $key_prefix);
        $time  = (int) apply_filters('lastudio-kit/rate-limit/window', MINUTE_IN_SECONDS, $key_prefix);

        if ( $limit <= 0 || $time <= 0 ) {
            return true;
        }

        $key  = $key_prefix . md5($user_ip);
        $data = get_transient($key);

        $start_window = static function() use ( $key, $time ) {
            set_transient( $key, ['count' => 1, 'start' => time()], $time );
            return true;
        };

        if ( ! is_array($data) || ! isset($data['count'], $data['start']) ) {
            return $start_window();
        }

        $elapsed = time() - (int) $data['start'];

        // Window rolled over (or the clock moved): begin a fresh one.
        if ( $elapsed >= $time || $elapsed < 0 ) {
            return $start_window();
        }

        $data['count'] = (int) $data['count'] + 1;

        // Persist the counter even once the limit is hit, and never with a non-positive TTL:
        // set_transient() treats an expiration <= 0 as "never expires", which would leave the
        // address locked out permanently.
        set_transient( $key, $data, max( 1, $time - $elapsed ) );

        if ( $data['count'] > $limit ) {
            return new \WP_Error(
                'rate_limited',
                'Too many requests. Please wait a moment.',
                ['status' => 429]
            );
        }

        return true;
    }
}

Template_Helper::get_instance();