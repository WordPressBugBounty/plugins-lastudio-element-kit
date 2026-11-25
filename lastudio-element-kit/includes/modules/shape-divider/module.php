<?php
namespace LaStudioKitThemeBuilder\Modules\ShapeDivider;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Module extends \Elementor\Core\Base\Module {

    public static function is_active() {
        return true;
    }

    public function get_name() {
        return 'lakit-eshape-divider';
    }

    public function __construct() {
        parent::__construct();

        add_action( 'elementor/shapes/additional_shapes', [ $this, 'register_additional_shape' ] );

        add_action( 'elementor/element/container/section_shape_divider/before_section_end', [ $this, 'register_media_controls' ], 10, 2 );
        add_action( 'elementor/element/section/section_shape_divider/before_section_end', [ $this, 'register_media_controls' ], 10, 2 );

        add_filter( 'elementor/frontend/container/should_render', [ $this, 'start_output_buffer' ], 10, 2 );
        add_filter( 'elementor/frontend/section/should_render', [ $this, 'start_output_buffer' ], 10, 2 );
        add_action( 'elementor/frontend/after_render', [ $this, 'replace_shape_svg_output' ], 10, 1 );

        add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_preview_scripts' ] );
    }


    public function register_additional_shape( $additional_shapes ){

        $placeholder_svg = plugin_dir_path( __FILE__ ) . 'assets/placeholder.svg';
        $placeholder_url = plugin_dir_url( __FILE__ ) . 'assets/placeholder.svg';

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
        $lakit_shapes['custom_svg'] = [
            'title'         =>  esc_html_x( 'Custom SVG (Upload)', 'Shapes', 'lastudio-kitr' ),
            'has_negative'  => true,
            'has_flip'      => true,
            'path'          => $placeholder_svg,
            'url'           => $placeholder_url,
        ];
        return array_merge( $additional_shapes, $lakit_shapes );
    }

    public function register_media_controls( $element, $args ) {
        foreach ( [ 'top', 'bottom' ] as $side ) {
            if ( ! $element->get_controls( "shape_divider_{$side}" ) ) {
                continue;
            }

            $element->start_injection( [
                'of' => "shape_divider_{$side}",
                'at' => 'after',
            ] );

            $element->add_control(
                "shape_divider_{$side}_custom_svg",
                [
                    'label'       => esc_html__( 'Upload SVG', 'custom-shape-divider' ),
                    'type'        => \Elementor\Controls_Manager::MEDIA,
                    'media_types' => [ 'svg' ],
                    'condition'   => [
                        "shape_divider_{$side}" => 'custom_svg',
                    ],
                    'editor_available' => true,
                    'render_type'      => 'none',
                ]
            );

            $element->end_injection();
        }
    }

    public function start_output_buffer( $should_render, $element ) {
        if ( ! $should_render ) {
            return $should_render;
        }

        if ( $this->has_custom_shape( $element ) ) {
            ob_start();
        }

        return $should_render;
    }

    public function replace_shape_svg_output( $element ) {

        if ( ! in_array( $element->get_type(), [ 'container', 'section' ], true ) || ! $this->has_custom_shape( $element ) ) {
            return;
        }

        $html = ob_get_clean();
        if ( ! $html ) {
            return;
        }

        $settings = $element->get_settings_for_display();

        foreach ( [ 'top', 'bottom' ] as $side ) {
            if ( ( $settings[ "shape_divider_{$side}" ] ?? '' ) !== 'custom_svg' ) {
                continue;
            }

            $svg_content = $this->get_uploaded_svg_content( $settings[ "shape_divider_{$side}_custom_svg" ] ?? [] );

            $pattern = '/(<div\b[^>]*class="[^"]*\belementor-shape-' . $side . '\b[^"]*"[^>]*>)(.*?)(<\/div>)/is';
            $html    = preg_replace_callback( $pattern, function( $matches ) use ( $svg_content ) {
                return $matches[1] . $svg_content . $matches[3];
            }, $html, 1 );
        }

        echo $html;
    }

    private function has_custom_shape( $element ) {
        $settings = $element->get_settings_for_display();
        return ( ( $settings['shape_divider_top'] ?? '' ) === 'custom_svg' )
            || ( ( $settings['shape_divider_bottom'] ?? '' ) === 'custom_svg' );
    }

    private function get_uploaded_svg_content( $svg_media ) {
        $attachment_id = $svg_media['id'] ?? 0;
        if ( ! $attachment_id ) {
            return '';
        }

        $file_path = get_attached_file( $attachment_id );
        if ( ! $file_path || ! file_exists( $file_path ) || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
            return '';
        }

        $svg = file_get_contents( $file_path );
        if ( ! $svg ) {
            return '';
        }
        
        $svg = preg_replace( '/<\?xml.*?\?>/i', '', $svg );

        if ( stripos( $svg, 'preserveAspectRatio' ) === false ) {
            $svg = preg_replace( '/<svg\b/i', '<svg preserveAspectRatio="none"', $svg, 1 );
        }

        $svg = preg_replace_callback( '/<(path|polygon|rect|circle|ellipse)\b([^>]*)>/i', function( $matches ) {
            $tag   = $matches[1];
            $attrs = $matches[2];

            if ( preg_match( '/\bfill\s*=\s*["\']none["\']/i', $attrs ) ) {
                return $matches[0];
            }

            $attrs = preg_replace( '/\s*\bfill\s*=\s*["\'][^"\']*["\']/i', '', $attrs );

            if ( preg_match( '/\bclass\s*=\s*["\']([^"\']*)["\']/i', $attrs ) ) {
                $attrs = preg_replace( '/\bclass\s*=\s*["\']([^"\']*)["\']/i', 'class="$1 elementor-shape-fill"', $attrs );
            } else {
                $attrs .= ' class="elementor-shape-fill"';
            }

            return "<{$tag}{$attrs}>";
        }, $svg );

        return trim( $svg );
    }

    public function enqueue_preview_scripts() {
        wp_enqueue_script(
            'lakit-admin-e-shape-divider',
            plugin_dir_url( __FILE__ ) . 'assets/js/preview-shapes.js',
            [ 'elementor-frontend', 'jquery' ],
            '1.0.2',
            true
        );

        wp_localize_script(
            'lakit-admin-e-shape-divider',
            'lakitShapeDividerPreview',
            [
                'placeholders' => [
                    plugin_dir_url( __FILE__ ) . 'assets/placeholder.svg',
                    plugin_dir_url( __FILE__ ) . 'assets/placeholder-negative.svg',
                ],
            ]
        );
    }

}
