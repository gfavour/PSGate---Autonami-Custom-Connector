<?php

final class BWFAN_Psgate_Integration extends BWFAN_Integration {

	private static $ins       = null;
	protected $connector_slug = 'bwfco_psgate';
	protected $need_connector = true;

	public function __construct() {
		$this->action_dir = __DIR__;
		$this->nice_name  = __( 'Psgate', 'autonami-automations-connectors' );
		$this->group_name = __( 'Messaging', 'autonami-automations-connectors' );
		$this->group_slug = 'messaging';
		$this->priority   = 5;

		add_filter( 'bwfan_sms_services', array( $this, 'add_as_sms_service' ), 10, 1 );
	}

	/**
	 * @return BWFAN_Twilio_Integration|null
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	protected function do_after_action_registration( BWFAN_Action $action_object ) {
		$action_object->connector = $this->connector_slug;
	}

	/**
	 * Add this integration to SMS services list.
	 *
	 * @param $sms_services
	 *
	 * @return array
	 */
	public function add_as_sms_service( $sms_services ) {
		$slug = $this->get_connector_slug();
		if ( BWFAN_Core()->connectors->is_connected( $slug ) ) {
			$integration                  = $slug;
			$sms_services[ $integration ] = $this->nice_name;
		}

		return $sms_services;
	}

	/** All SMS Providers must expose this function as API to send message */
	public function send_message( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'to'        => '',
				'body'      => '',
				'image_url' => '',
			)
		);

		$to   = $args['to'];
		$body = $args['body'];
		if ( empty( $to ) || empty( $body ) ) {
			return new WP_Error( 400, 'Data missing to send psgate SMS' );
		}

		WFCO_Common::get_connectors_data();
		$settings    = WFCO_Common::$connectors_saved_data[ $this->get_connector_slug() ];
		$account_sid = $settings['username'];
		$auth_token  = $settings['password'];
		$psgate_no   = $settings['psgate_no'];
		if ( empty( $account_sid ) || empty( $auth_token ) || empty( $psgate_no ) ) {
			return new WP_Error( 404, 'Invalid / Missing saved connector data' );
		}

		$call_args = array(
			'username' => $account_sid,
			'password'  => $auth_token,
			'psgate_no'   => $psgate_no,
			'phone'       => $to,
			'sms_body'    => $body,
		);
		
		
		$image_url = $args['image_url'];
		if ( ! empty( $image_url ) && filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			$call_args['mediaUrl'] = $image_url;
		}

		$load_connectors = WFCO_Load_Connectors::get_instance();
		$call = $load_connectors->get_call( 'wfco_psgate_send_sms' );
		$call->set_data( $call_args );
		$response = $call->process(); //my code
		
		return true; //$this->validate_send_message_response( $call->process() );
	}

	public function validate_send_message_response( $response ) {
		$is_api_error = isset( $response['body']['error_message'] ) && ! empty( $response['body']['error_message'] );
		if ( is_array( $response ) && 200 === $response['response'] && ! $is_api_error ) {
			return true;
		}

		$message = __( 'SMS could not be sent. ', 'autonami-automations-connectors' );

		if ( isset( $response['body']['errors'] ) && isset( $response['body']['errors'][0] ) && isset( $response['body']['errors'][0]['message'] ) ) {
			$message = $response['body']['errors'][0]['message'];
		} elseif ( isset( $response['body']['message'] ) ) {
			$message = $response['body']['message'];
		} elseif ( isset( $response['body']['error_message'] ) ) {
			$message = $response['body']['error_message'];
		} elseif ( isset( $response['bwfan_response'] ) && ! empty( $response['bwfan_response'] ) ) {
			$message = $response['bwfan_response'];
		} elseif ( is_array( $response['body'] ) && isset( $response['body'][0] ) && is_string( $response['body'][0] ) ) {
			$message = $message . $response['body'][0];
		}

		return new WP_Error( 500, $message );
	}

}

BWFAN_Load_Integrations::register( 'BWFAN_Psgate_Integration' );
