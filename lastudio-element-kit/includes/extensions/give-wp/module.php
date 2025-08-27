<?php

namespace LaStudioKitExtensions\GiveWP;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Core\DynamicTags\Manager as DynamicTagsManager;
use LaStudioKitExtensions\Module_Base;

class Module extends Module_Base {

    /**
     * Module version.
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module directory path.
     *
     * @since 1.0.0
     * @access protected
     * @var string $path
     */
    protected $path;

    /**
     * Module directory URL.
     *
     * @since 1.0.0
     * @access protected
     * @var string $url.
     */
    protected $url;

    public static function is_active(){
        return class_exists('Give', false);
    }

	public function template_include($template) {
		if(is_singular('give_forms')){
			$file     = locate_template( array(
				'single-givewp.php',
				'single-elementor_library.php',
				'index.php',
			) );
			if($file){
				return $file;
			}
		}
		return $template;
	}

	public function fix_featured_image_for_giveform( $thumbnail_id, $post ){
		static $did_process = [];
		if($post->post_type === 'give_forms' && !$thumbnail_id){
			if(isset($did_process[$post->ID])){
				return $did_process[$post->ID];
			}
			$did_process[$post->ID] = $thumbnail_id;
			$formBuilderSettings = give()->form_meta->get_meta($post->ID, 'formBuilderSettings', true);
			if(!empty($formBuilderSettings)){
				$formBuilderSettings = json_decode($formBuilderSettings, true);
				if( !empty( $formBuilderSettings['designSettingsImageUrl'] ) ){
					$found_id = attachment_url_to_postid($formBuilderSettings['designSettingsImageUrl']);
					if(!empty($found_id)){
						$did_process[$post->ID] = $found_id;
						return $found_id;
					}
				}
			}
		}
		return $thumbnail_id;
	}

	public function fix_error_print_emoji_styles() {
		if ( has_action( 'wp_print_styles', 'print_emoji_styles' ) ) {
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
		}
	}

	public function add_style_for_iframe() {
		wp_enqueue_style(
			'lakit-givewp-form-iframe',
			apply_filters('lastudio-kit/givewp/style_url', lastudio_kit()->plugin_url('assets/css/lakit-givewp.min.css')),
			[],
			lastudio_kit()->get_version()
		);
	}

	public function fix_admin_title() {
		global $title, $plugin_page, $pagenow;
		if( $pagenow === 'edit.php' && $plugin_page === 'givewp-form-builder' && isset( $_GET['donationFormID'], $_GET['post_type'] ) && $_GET['post_type'] === 'give_forms' ){
			$title = 'Form';
		}
	}

    public function givewp_form_builder_enqueue_scripts()
    {
        $donationFormId = abs($_GET['donationFormID'] ?? 0);
        if( 'give_forms' === get_post_type($donationFormId)){
            wp_enqueue_script(
                'lakit-givewp-admin',
                lastudio_kit()->plugin_url('includes/extensions/give-wp/assets/js/bundle.js'),
                [],
                lastudio_kit()->get_version(),
                true
            );
            $customContent = give()->form_meta->get_meta($donationFormId, '_lakit_custom_content', true);
            $elementor_edit_mode = give()->form_meta->get_meta($donationFormId, '_elementor_edit_mode', true);
            wp_localize_script('lakit-givewp-admin', 'lakitGiveVar', [
                'editUrl' => add_query_arg([
                    'post' => $donationFormId,
                    'action' => 'elementor'
                ], admin_url('post.php') ),
                'customContent'     => $customContent,
                'enableElementor'   => $elementor_edit_mode === 'builder',
            ]);
        }
    }

    /**
     * @param \Give\DonationForms\Models\DonationForm $form
     * @param \WP_REST_Request $request
     * @return void
     */
    public function givewp_form_builder_updated( $form, $request )
    {
        $setting_raw        = $request->get_param('settings');
        $settings           = json_decode($setting_raw, true);
        $lakitSettings      = $settings['lakitSettings'] ?? [];
        $customContent      = $lakitSettings['customContent'] ?? '';
        $enableElementor    = filter_var($lakitSettings['enableElementor'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $donationFormId     = $form->id;
        give()->form_meta->update_meta($donationFormId, '_lakit_custom_content', $customContent);
        give()->form_meta->update_meta($donationFormId, '_elementor_edit_mode', $enableElementor ? 'builder' : '');
    }

	public function register_tag( DynamicTagsManager $dynamic_tags ) {
		$tag = __NAMESPACE__ . '\DynamicTags\Tag';

		$dynamic_tags->register( new $tag() );
	}

    public function __construct()
    {
        $this->path = lastudio_kit()->plugin_path('includes/extensions/give-wp/');
        $this->url  = lastudio_kit()->plugin_url('includes/extensions/give-wp/');

	    add_action( 'elementor/dynamic_tags/register', [ $this, 'register_tag' ] );

	    add_action( 'elementor/widgets/register', function ($widgets_manager){
		    $widgets_manager->register( new Widgets\GiveFormGrid() );
		    $widgets_manager->register( new Widgets\GiveFormGoal() );
		    $widgets_manager->register( new Widgets\GiveFormDonate() );
		    $widgets_manager->register( new Widgets\GiveFormDonateButton() );
	    } );
	    add_filter('template_include', [ $this, 'template_include' ], 20);
	    add_filter('post_thumbnail_id', [ $this, 'fix_featured_image_for_giveform' ], 20, 2);
//	    add_filter('give_disable_hook-admin_menu:Give\DonationForms\V2\DonationFormsAdminPage@register', '__return_true');
		add_action('givewp_donation_confirmation_receipt_showing', [ $this, 'fix_error_print_emoji_styles' ]);
		add_action('givewp_donation_form_enqueue_scripts', [ $this, 'fix_error_print_emoji_styles' ]);
		add_action('givewp_donation_form_enqueue_scripts', [ $this, 'add_style_for_iframe' ]);
		add_action('give_embed_head', [ $this, 'add_style_for_iframe' ]);
		add_action('admin_init', [ $this, 'fix_admin_title' ]);
        add_action('givewp_form_builder_enqueue_scripts', [ $this, 'givewp_form_builder_enqueue_scripts' ]);
        add_action('givewp_form_builder_updated', [ $this, 'givewp_form_builder_updated' ], 20, 2);
    }
}