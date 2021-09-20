<?php

class BWFAN_Psgate_SMS_State extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'psgate_sms_state';
		$this->tag_description = __( 'Psgate SMS Sender State', 'autonami-automations' );
		add_shortcode( 'bwfan_psgate_sms_state', array( $this, 'parse_shortcode' ) );
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
		$value       = isset( $psgate_data['FromState'] ) && ! empty( $psgate_data['FromState'] ) ? $psgate_data['FromState'] : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'FL', 'autonami-automations' );
	}
}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'psgate_sms', 'BWFAN_Psgate_SMS_State' );
