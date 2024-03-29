<?php

final class BWFAN_Psgate_Source extends BWFAN_Source {
	// source type contains slug of current source. this helps events to become a child of a source
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	protected function __construct() {
		$this->event_dir  = __DIR__;
		$this->nice_name  = __( 'Psgate', 'autonami-automations-connectors' );
		$this->group_name = __( 'Messaging', 'wp-marketing-automations' );
		$this->group_slug = 'messaging';
		$this->priority   = 50;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Twilio_Source|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

/**
 * Register this as a source.
 */

$saved_connectors = WFCO_Common::$connectors_saved_data;

if ( empty( $saved_connectors ) ) {
	WFCO_Common::get_connectors_data();
	$saved_connectors = WFCO_Common::$connectors_saved_data;
}

if ( array_key_exists( 'bwfco_psgate', $saved_connectors ) && class_exists( 'BWFAN_Load_Sources' ) ) {
	BWFAN_Load_Sources::register( 'BWFAN_Psgate_Source' );
}
