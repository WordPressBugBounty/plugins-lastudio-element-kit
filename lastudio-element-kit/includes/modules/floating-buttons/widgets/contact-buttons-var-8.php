<?php

namespace LaStudioKitThemeBuilder\Modules\FloatingButtons\Widgets;

use Elementor\Core\Base\Providers\Social_Network_Provider;
use LaStudioKitThemeBuilder\Modules\FloatingButtons\Base\Widget_Contact_Button_Base_Pro;
use LaStudioKitThemeBuilder\Modules\FloatingButtons\Classes\Render\Contact_Buttons_Var_8_Render;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Contact_Buttons_Var_8 extends Widget_Contact_Button_Base_Pro {

	public static function get_configuration() {
		$config = parent::get_configuration();
		$config['style']['has_platform_colors'] = false;
		$config['content']['chat_button_section']['section_name'] = esc_html__( 'Floating Button', 'lastudio-kit' );
		$config['content']['chat_button_section']['has_platform'] = false;
		$config['content']['chat_button_section']['has_icon'] = true;
		$config['content']['chat_button_section']['icon_default'] = [
			'value' => 'fas fa-life-ring',
			'library' => 'fa-solid',
		];
		$config['content']['chat_button_section']['icons_recommended'] = [
			'fa-solid' => [
				'life-ring',
				'info',
				'question',
			],
			'fa-regular' => [
				'info',
				'question',
				'comment-alt',
			],
		];
		$config['content']['chat_button_section']['has_notification_dot'] = false;
		$config['content']['chat_button_section']['has_active_tab'] = true;

		$config['content']['top_bar_section']['title']['label'] = esc_html__( 'Greeting', 'lastudio-kit' );
		$config['content']['top_bar_section']['title']['default'] = __( 'We\'re here for you', 'lastudio-kit' );
		$config['content']['top_bar_section']['title']['placeholder'] = esc_html__( 'Enter your text here', 'lastudio-kit' );
		$config['content']['top_bar_section']['title']['dynamic'] = true;
		$config['content']['top_bar_section']['title']['ai'] = true;
		$config['content']['top_bar_section']['title']['label_block'] = true;
		$config['content']['top_bar_section']['subtitle']['label'] = esc_html__( 'Call to Action', 'lastudio-kit' );
		$config['content']['top_bar_section']['subtitle']['default'] = esc_html__( 'Explore our resources', 'lastudio-kit' );
		$config['content']['top_bar_section']['subtitle']['placeholder'] = esc_html__( 'Enter your text here', 'lastudio-kit' );
		$config['content']['top_bar_section']['subtitle']['dynamic'] = true;
		$config['content']['top_bar_section']['subtitle']['ai'] = true;
		$config['content']['top_bar_section']['subtitle']['label_block'] = true;
		$config['content']['top_bar_section']['has_image'] = false;
		$config['content']['top_bar_section']['has_active_dot'] = false;
		$config['style']['top_bar_section']['title_heading_label'] = esc_html__( 'Greeting', 'lastudio-kit' );
		$config['style']['top_bar_section']['subtitle_heading_label'] = esc_html__( 'Call to Action', 'lastudio-kit' );
		$config['style']['top_bar_section']['has_style_close_button'] = false;

		$config['content']['contact_section']['platform']['limit'] = null;
		$config['content']['contact_section']['section_name'] = esc_html__( 'Resource Links', 'lastudio-kit' );
		$config['content']['contact_section']['platform']['group-1'] = [
			Social_Network_Provider::EMAIL,
			Social_Network_Provider::TELEPHONE,
			Social_Network_Provider::SMS,
			Social_Network_Provider::WHATSAPP,
			Social_Network_Provider::SKYPE,
			Social_Network_Provider::MESSENGER,
			Social_Network_Provider::VIBER,
			Social_Network_Provider::WAZE,
			Social_Network_Provider::URL,
		];
		$config['content']['contact_section']['default'] = [
			[
				'contact_icon_platform' => Social_Network_Provider::WHATSAPP,
			],
			[
				'contact_icon_platform' => Social_Network_Provider::MESSENGER,
			],
			[
				'contact_icon_platform' => Social_Network_Provider::EMAIL,
			],
			[
				'contact_icon_platform' => Social_Network_Provider::TELEPHONE,
			],
		];
		$config['content']['contact_section']['has_cta_text'] = false;
		$config['content']['contact_section']['repeater']['has_title'] = true;
		$config['content']['contact_section']['repeater']['has_description'] = true;

		$config['style']['chat_box_section']['section_name'] = esc_html__( 'Box', 'lastudio-kit' );
		$config['style']['send_button_section']['has_typography'] = false;

		return $config;
	}

	public function get_name(): string {
		return 'contact-buttons-var-8';
	}

	public function get_title(): string {
		return esc_html__( 'Resource Box', 'lastudio-kit' );
	}

	protected function add_content_tab(): void {
		$this->add_chat_button_section();

		$this->add_top_bar_section();

		$this->add_contact_section();
	}

	protected function add_style_tab(): void {
		$this->add_style_chat_button_section();

		$this->add_style_top_bar_section();

		$this->add_style_resource_links_section();

		$this->add_style_chat_box_section();
	}

	public function render(): void {
		$render_strategy = new Contact_Buttons_Var_8_Render( $this );

		$render_strategy->render();
	}

}
