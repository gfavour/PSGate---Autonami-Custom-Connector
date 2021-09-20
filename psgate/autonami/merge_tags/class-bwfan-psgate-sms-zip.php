<?php

class BWFAN_Psgate_SMS_Zip extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'psgate_sms_zip';
		$this->tag_description = __( 'Psgate SMS Sender Zip', 'autonami-automations' );
		add_shortcode( 'bwfan_psgate_sms_zip', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		$psgate_data = BWFAN_Merge_Tag_Loader::get_data( 'psgate_data' );
		$value       = isset( $psgate_data['FromZip'] ) && ! empty( $psgate_data['FromZip'] ) ? $psgate_data['FromZip'] : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( '32250', 'autonami-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'psgate_sms', 'BWFAN_Psgate_SMS_Zip' );
