<?php

class BWFAN_Psgate_SMS_SID extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'psgate_sms_sid';
		$this->tag_description = __( 'Psgate SMS SID', 'autonami-automations' );
		add_shortcode( 'bwfan_psgate_sms_sid', array( $this, 'parse_shortcode' ) );
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
		$value       = isset( $psgate_data['SmsSid'] ) && ! empty( $psgate_data['SmsSid'] ) ? $psgate_data['SmsSid'] : '';

		return $this->parse_shortcode_output( $value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( '123', 'autonami-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'psgate_sms', 'BWFAN_Psgate_SMS_SID' );
