<?php
// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter('elementor/editor/localize_settings', function ($configs){
    $key = ['elementor_site', 'docs_elementor_site', 'help_the_content_url', 'help_right_click_url', 'help_flexbox_bc_url', 'elementPromotionURL', 'dynamicPromotionURL'];
    $key2 = ['help_preview_error_url', 'help_preview_http_error_url', 'help_preview_http_error_500_url', 'goProURL'];
    $tmp = [];
    if(is_array($configs)){
        foreach ($configs as $k => $v){
            if(in_array($k, $key)){
                $old_val = $configs[$k];
                $tmp[] = $old_val;
	            $configs[$k] = 'https://la-studioweb.com/go/elementor/docs';
            }
            if( ($k == 'preview' || $k == 'icons') && is_array($v) ){
                foreach ($v as $k1 => $v1){
                    if(in_array($k1, $key2)){
                        $old_val2 = $v[$k1];
                        $tmp[] = $old_val2;
	                    $v[$k1] = 'https://la-studioweb.com/go/elementor/docs';
                    }
                }
                $configs[$k] = $v;
            }
        }
    }
    if(!empty($configs['initial_document']['widgets'])){
        foreach ($configs['initial_document']['widgets'] as $widget => &$setting ) {
            if(isset($setting['help_url'])){
	            $setting['help_url'] = 'https://la-studioweb.com/go/elementor/docs';
            }
        }
    }
    if(!empty($configs['elements'])){
        foreach ($configs['elements'] as $widget_name => &$setting ) {
            if(isset($setting['help_url'])){
	            $setting['help_url'] = 'https://la-studioweb.com/go/elementor/docs';
            }
        }
    }

    if( !empty($configs['promotion']['elements']['action_button']['url']) ){
        $configs['promotion']['elements']['action_button']['url'] = str_replace('https://go.elementor.com', 'https://la-studioweb.com/go/elementor', $configs['promotion']['elements']['action_button']['url']);
    }

    return $configs;
});

add_action('elementor/app/init', function (){
	/**
	 * Remove the deprecated `print_emoji_styles` handler.
	 * It avoids breaking style generation with a deprecation message.
	 */
	$has_emoji_styles = has_action( 'wp_print_styles', 'print_emoji_styles' );
	if ( $has_emoji_styles ) {
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
    add_action('wp_print_footer_scripts', function (){
        ?>
        <script type="text/javascript">
            function LaStudioKit_ReplaceLinks2(){
                document.querySelectorAll('a[href*="go.elementor.com"]').forEach( elm => {
                    elm.setAttribute('href', 'https://la-studioweb.com/go/elementor-pro')
                } )
                document.querySelectorAll('a[href*="elementor.com/popup-builder"]').forEach( elm => {
                    elm.setAttribute('href', 'https://la-studioweb.com/go/elementor/popup-builder')
                } )
            }
            document.addEventListener('DOMContentLoaded', LaStudioKit_ReplaceLinks2);
            window.addEventListener('load', LaStudioKit_ReplaceLinks2);
        </script>
        <?php
    }, 999);
});

add_action('admin_footer', function (){
    ?>
    <script type="text/javascript">
        function LaStudioKit_ReplaceLinks2(){
            document.querySelectorAll('a[href*="go.elementor.com"]').forEach( elm => {
                elm.setAttribute('href', 'https://la-studioweb.com/go/elementor-pro')
            } )
            document.querySelectorAll('a[href*="elementor.com/popup-builder"]').forEach( elm => {
                elm.setAttribute('href', 'https://la-studioweb.com/go/elementor/popup-builder')
            } )
        }
        document.addEventListener('DOMContentLoaded', LaStudioKit_ReplaceLinks2);
        window.addEventListener('load', LaStudioKit_ReplaceLinks2);
    </script>
    <?php
}, 999);

add_filter('wp_redirect', function ( $location ){
    if( strpos($location, 'https://elementor.com/pro') !== false || strpos($location, '//go.elementor.com') !== false ){
        $location = 'https://la-studioweb.com/go/elementor-pro';
    }
    return $location;
}, 20);

add_action('elementor/editor/footer', function (){
    ?>
    <script type="text/javascript">
        const LaStudioKit_ScriptTemplateIds = ['#tmpl-elementor-panel-categories', '#tmpl-elementor-panel-global', '#tmpl-elementor-template-library-get-pro-button', '#elementor-preview-responsive-wrapper #elementor-notice-bar'];
        LaStudioKit_ScriptTemplateIds.forEach(function (id){
            const temp = document.querySelector(id);
            if(temp){
                temp.innerHTML = temp.innerHTML.replace(/href="(.*?)"/gi, 'href="https://la-studioweb.com/go/elementor-pro"');
            }
        });
        function LaStudioKit_ReplaceLinks(){
            document.querySelectorAll('a[href*="go.elementor.com"]').forEach( elm => {
                elm.setAttribute('href', 'https://la-studioweb.com/go/elementor/docs')
            } )
        }
        document.addEventListener('DOMContentLoaded', LaStudioKit_ReplaceLinks);
        window.addEventListener('load', LaStudioKit_ReplaceLinks);
    </script>
    <?php
}, 100);