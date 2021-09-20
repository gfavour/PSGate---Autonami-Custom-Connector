<?php

class  BWFCO_Psgate extends BWF_CO {

	private static $ins = null;
	public $is_setting = true;
	public $v2 = true;

	public function __construct() {
		$this->dir               = __DIR__;
		$this->autonami_int_slug = 'psgate_integration';
		$this->connector_url     = WFCO_AUTONAMI_CONNECTORS_PLUGIN_URL . '/connectors/psgate';
		$this->keys_to_track     = [
			'username',
			'password',
			'psgate_no',
		];
		$this->form_req_keys     = [
			'username',
			'password',
			'psgate_no',
		];

		add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
		$this->include_files();
	}

	public function include_files() {
		include_once __DIR__ . '/includes/class-bwfan-psgate-webook-setup.php';
		include_once __DIR__ . '/includes/class-bwfan-phone-number.php';
	}

	/**
	 * @return BWFCO_Psgate|null
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function add_card( $available_connectors ) {
		$available_connectors['autonami']['connectors']['bwfco_psgate'] = array(
			'name'            => 'Psgate',
			'desc'            => __( 'Engage your customers via SMS, a marketing channel with a high engagement rate.', 'autonami-automations-connectors' ),
			'connector_class' => 'BWFCO_Psgate',
			'image'           => $this->get_image(),
			'source'          => '',
			'file'            => '',
		);

		return $available_connectors;
	}

	/**
	 * This function connects to the automation and fetch the data required for the actions on automations screen to work properly.
	 *
	 * @param $posted_data
	 *
	 * @return array|int
	 */
	public function get_api_data( $posted_data ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_psgate_oauth_check' );

		$resp_array = array(
			'api_data' => $posted_data,
			'status'   => 'failed',
			'message'  => __( 'There was problem authenticating your account. Confirm entered details.', 'autonami-automations-connectors' ),
		);

		if ( is_null( $call_class ) ) {
			return $resp_array;

		}

		$data = array(
			'username' => isset( $posted_data['username'] ) ? $posted_data['username'] : '',
			'password'  => isset( $posted_data['password'] ) ? $posted_data['password'] : '',
		);

		$call_class->set_data( $data );
		$ac_status = $call_class->process();
		//$ac_status = ['response'=>200];
		
		if ( is_array( $ac_status ) && 200 === $ac_status['response'] ) {
			$response                            = [];
			$response['status']                  = 'success';
			$response['api_data']['username'] = $posted_data['username'];
			$response['api_data']['password']  = $posted_data['password'];
			$response['api_data']['psgate_no']   = $posted_data['psgate_no'];

			return $response;

		} else {
			$resp_array['status']  = 'failed';
			$resp_array['message'] = isset( $ac_status['body']['message'] ) ? $ac_status['body']['message'] : __( 'Undefined Api Error', 'autonami-automations-connectors' );

			return $resp_array;
		}
	}

	public function get_fields_schema() {
		return array(
			array(
				'id'          => 'username',
				'label'       => __( 'Enter Account Username', 'wp-marketing-automations-connectors' ),
				'type'        => 'text',
				'class'       => 'bwfan_psgate_account_sid',
				'placeholder' => __( 'Account Username', 'wp-marketing-automations-connectors' ),
				'required'    => true,
				'toggler'     => array(),
			),
			array(
				'id'          => 'password',
				'label'       => __( 'Enter Account Password', 'wp-marketing-automations-connectors' ),
				'type'        => 'password',
				'class'       => 'bwfan_psgate_auth_token',
				'placeholder' => __( 'Account Password', 'wp-marketing-automations-connectors' ),
				'required'    => true,
				'toggler'     => array(),
			),
			array(
				'id'          => 'psgate_no',
				'label'       => __( 'Sender', 'wp-marketing-automations-connectors' ),
				'type'        => 'text',
				'class'       => 'bwfan_psgate_number',
				'placeholder' => __( 'Sender', 'wp-marketing-automations-connectors' ),
				'required'    => true,
				'toggler'     => array(),
			),
		);
	}

	public function get_settings_fields_values() {
		$saved_data = WFCO_Common::$connectors_saved_data;
		$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();

		$vals = array();
		if ( isset( $old_data['username'] ) ) {
			$vals['username'] = $old_data['username'];
		}
		if ( isset( $old_data['password'] ) ) {
			$vals['password'] = $old_data['password'];
		}
		if ( isset( $old_data['psgate_no'] ) ) {
			$vals['psgate_no'] = $old_data['psgate_no'];
		}
		return $vals;
	}

}

WFCO_Load_Connectors::register( 'BWFCO_Psgate' );
