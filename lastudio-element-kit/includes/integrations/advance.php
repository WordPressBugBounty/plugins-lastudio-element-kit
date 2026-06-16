<?php
// Prevent direct access.
if ( ! defined( 'WPINC' ) ) {
    exit;
}

final class LaStudioKit_Elementor_Link_Replacer {

    private const TARGET_URL = 'https://la-studioweb.com/go/elementor-pro';

    private const CONFIG_VARS = [
        'elementorCommonConfig',
        'elementorAppConfig',
        'elementorPromotionsData',
        'editorOneSidebarConfig',
    ];

    private const TEMPLATE_SELECTORS = [
        '#tmpl-elementor-panel-revisions-no-revisions',
        '#tmpl-elementor-panel-categories',
        '#tmpl-elementor-panel-global',
        '#tmpl-elementor-template-library-save-template',
        '#tmpl-elementor-template-library-save-template-variant-b',
        '#tmpl-elementor-template-library-import',
        '#tmpl-elementor-template-library-templates-empty',
        '#tmpl-elementor-navigator',
        '#tmpl-elementor-template-library-get-pro-button',
        '#elementor-preview-responsive-wrapper #elementor-notice-bar',
    ];

    public static function init() {
        add_filter( 'elementor/editor/localize_settings', [ __CLASS__, 'replace_links_recursive' ], 100 );
        add_filter( 'elementor/common/localize_settings', [ __CLASS__, 'replace_links_recursive' ], 100 );
        add_filter( 'wp_redirect', [ __CLASS__, 'replace_redirect_url' ], 20 );

        // Remove deprecated emoji style.
        add_action( 'elementor/app/init', static function () {
            if ( has_action( 'wp_print_styles', 'print_emoji_styles' ) ) {
                remove_action( 'wp_print_styles', 'print_emoji_styles' );
            }
        });
        add_action( 'admin_footer', [ __CLASS__, 'print_script' ], 999 );
        add_action( 'elementor/editor/footer', [ __CLASS__, 'print_script' ], 999 );
    }
    public static function replace_links_recursive( $data ) {
        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                $data[ $key ] = self::replace_links_recursive( $value );
            }
            return $data;
        }
        if ( is_object( $data ) ) {
            foreach ( $data as $key => $value ) {
                $data->$key = self::replace_links_recursive( $value );
            }
            return $data;
        }
        if ( is_string( $data ) ) {
            $data = preg_replace( '#https?://(?:go\.)?elementor\.com/[^\s"\']+#i', self::TARGET_URL, $data );
        }
        return $data;
    }
    public static function replace_redirect_url( $location ) {
        if ( ! is_string( $location ) ) {
            return $location;
        }
        if ( str_contains( $location, 'elementor.com/pro' ) || str_contains( $location, 'go.elementor.com' ) ) {
            return self::TARGET_URL;
        }
        return $location;
    }

    public static function print_script() {
        static $printed = false;
        if ( $printed ) {
            return;
        }
        $printed = true;
        $config_vars = wp_json_encode( self::CONFIG_VARS );
        ?>
        <script type="text/javascript">
            (() => {
                const TARGET_URL = <?php echo wp_json_encode( self::TARGET_URL ); ?>;
                const TEMPLATE_SELECTORS = <?php echo wp_json_encode( self::TEMPLATE_SELECTORS ); ?>;
                const CONFIG_VARS = <?php echo $config_vars; ?>;
                function replaceData(data) {
                    if (Array.isArray(data)) {
                        return data.map(replaceData);
                    }
                    if (data && typeof data === 'object') {
                        Object.keys(data).forEach((key) => {
                            data[key] = replaceData(data[key]);
                        });
                        return data;
                    }
                    if (typeof data === 'string') {
                        return data.replace( /https?:\/\/(?:go\.)?elementor\.com\/[^\s"']*/gi, TARGET_URL );
                    }
                    return data;
                }
                function replaceTemplates() {
                    TEMPLATE_SELECTORS.forEach((selector) => {
                        const element = document.querySelector(selector);
                        if (!element) {
                            return;
                        }
                        element.innerHTML = element.innerHTML.replace(/https?:\/\/(?:go\.)?elementor\.com/gi,TARGET_URL);
                    });
                }
                function replaceLinks() {
                    replaceTemplates();
                    document.querySelectorAll('a[href*="go.elementor.com"]')?.forEach((element) => { element.href = TARGET_URL });
                    CONFIG_VARS.forEach((configVar) => {
                        if (typeof window[configVar] !== 'undefined') {
                            window[configVar] = replaceData(window[configVar]);
                        }
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', replaceLinks);
                }
                else {
                    replaceLinks();
                }
                window.addEventListener('load', replaceLinks);
            })();
        </script>
        <?php
    }
}
LaStudioKit_Elementor_Link_Replacer::init();