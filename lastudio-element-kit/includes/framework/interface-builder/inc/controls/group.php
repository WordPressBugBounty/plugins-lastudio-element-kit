<?php
/**
 * Class for the building ui-group elements.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'CX_Control_Group' ) ) {

	/**
	 * Class for the building ui-repeater elements.
	 */
	class CX_Control_Group extends CX_Controls_Base
    {

        /**
         * Default settings
         *
         * @var array
         */
        public $defaults_settings = array(
            'type' => 'group',
            'id' => 'cx-ui-group-id',
            'name' => 'cx-ui-group-name',
            'value' => array(),
            'fields' => array(),
            'label' => '',
            'add_label' => 'Add Item',
            'class' => '',
            'ui_kit' => true,
            'required' => false,
            'title_field' => '',
            'collapsed' => false,
        );

        /**
         * Render html UI_Group.
         *
         * @since 1.0.1
         */
        public function render()
        {
            $html = '<div class="cx-ui-group-container cx-ui-container cx-ui-kit">';
            foreach ( $this->settings['fields'] as $field ) {
                $field_classes = array(
                    $field['id'] . '-wrap',
                    'cx-ui-group-item-control'
                );

                if ( ! empty( $field['class'] ) ) {
                    $field_classes[] = $field['class'];
                }

                $field_classes = implode( ' ', $field_classes );
                $html .= '<div class="' . $field_classes . '" data-group-control-name="' . $field['id'] . '">';
                $html .= $this->render_field($field, $this->settings['value'] ?? []);
                $html .= '</div>';
            }
            $html .= '</div>';

            return $html;
        }

        /**
         * Render single repeater field
         *
         * @param  string $index        Current row index.
         * @param  number $widget_index It contains widget index.
         * @param  array  $field        Values to paste.
         * @return string
         */
        public function render_field( $field, $values ) {

            if ( empty( $field['type'] ) || empty( $field['name'] ) ) {
                return '"type" and "name" are required fields for UI_Group items';
            }

            $f_name = $field['name'];
            $_value = $values[$f_name] ?? '';

            $field = wp_parse_args( $field, array(
                'value' => $_value,
            ) );

            switch ( $field['type'] ) {

                case 'html':
                    return sprintf( '<div class="cx-ui-container">%s</div>', $field['html'] );

                default:

                    $ui_class_name  = 'CX_Control_' . ucwords( $field['type'] );

                    if ( ! class_exists( $ui_class_name ) ) {
                        return '<p>Class <b>' . $ui_class_name . '</b> not exist!</p>';
                    }

                    $ui_item = new $ui_class_name( $field );

                    return $ui_item->render();

            }

        }
    }
}
