<?php
/**
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Estate_Manager_Metabox extends Estate_Manager_k {

	private $screens = array( 'property' );

	private $general_fields = array(
		array(
			'id' => 'price',
			'label' => 'Price',
			'type' => 'text',
		),
		array(
			'id' => 'address',
			'label' => 'Address',
			'type' => 'text',
		),
	);

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_general_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_additional_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	public function add_general_meta_box() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'general-property-info',
				__( 'General Property Info', 'estatek' ),
				array( $this, 'general_meta_box_cb' ),
				$screen,
				'advanced',
				'default'
			);
		}
	}

	public function general_meta_box_cb( $post ) {
		wp_nonce_field( 'general_info_data', 'general_info_nonce' );
		// echo 'Description';
		$this->generate_general_fields( $post );
	}

	public function generate_general_fields( $post ) {
		$output = '';
		foreach ( $this->general_fields as $field ) {
			$label = '<label for="' . $field['id'] . '">' . __( $field['label'], 'estatek' ) . '</label>';
			$db_value = get_post_meta( $post->ID, 'general_info_' . $field['id'], true );
			switch ( $field['type'] ) {
				case 'checkbox':
					$input = sprintf(
						'<input %s id="%s" name="%s" type="checkbox" value="1">',
						$db_value === '1' ? 'checked' : '',
						$field['id'],
						$field['id']
					);
					break;
				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$field['type'] !== 'color' ? 'class="regular-text"' : '',
						$field['id'],
						$field['id'],
						$field['type'],
						$db_value
					);
			}
			$output .= $this->row_format( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	public function row_format( $label, $input ) {
		return sprintf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			$label,
			$input
		);
	}

	public function add_additional_meta_box() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'additional-property-info',
				__( 'Additional Property Info', 'estatek' ),
				array( $this, 'additional_meta_box_cb' ),
				$screen,
				'advanced',
				'default'
			);
		}
	}

	public function additional_meta_box_cb( $post ) {
		wp_nonce_field( 'additional_info_data', 'additional_info_nonce' );

		$db_value = get_post_meta( $post->ID, 'additional_info_' . $field['id'], true );
		?>
		<table class="form-table"><tbody>
		</tbody></table><?php
	}

	public function save_post( $post_id ) {
		if ( ! isset( $_POST['geneal_info_nonce'] ) || ! isset( $_POST['additional_info_nonce'] ) )
			return $post_id;

		$g_nonce = $_POST['geneal_info_nonce'];
		$a_nonce = $_POST['additional_info_nonce'];
		if ( !wp_verify_nonce( $g_nonce, 'geneal_info_data' ) || !wp_verify_nonce( $a_nonce, 'additional_info_data' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		foreach ( $this->general_fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						$_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
						break;
					case 'text':
						$_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
						break;
				}
				update_post_meta( $post_id, 'geneal_info_' . $field['id'], $_POST[ $field['id'] ] );
			} else if ( $field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, 'geneal_info_' . $field['id'], '0' );
			}
		}
	}
}