<?php

/**
 * Class BWFAN_Psgate_Message_Received
 */
final class BWFAN_Psgate_Message_Received extends BWFAN_Event {
	private static $instance = null;
	private $psgate_id = null;
	private $automation_key = '';
	private $localized_automation_key = '';
	private $psgate_data = array();

	private function __construct() {
		$this->optgroup_label         = __( 'Psgate', 'autonami-automations' );
		$this->event_name             = __( 'SMS Received', 'autonami-automations' );
		$this->event_desc             = __( 'This automation would trigger webhook, from Psgate Message Received.', 'autonami-automations' );
		$this->event_merge_tag_groups = array( 'psgate_sms' );
		$this->event_rule_groups      = array();
		$this->included_actions       = array(
			'wp_http_post',
			'wp_debug',
			'wp_custom_callback',
			'za_send_data',
			'psgate_send_sms',
			'sl_message_user',
			'sl_message'
		);
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'bwfan_webhook_admin_enqueue_assets' ), 98 );
		add_action( "bwfan_psgate_connector_sync_call", array( $this, 'before_process_webhook_contact' ), 10, 3 );

		add_action( 'bwfan_webhook_psgate_sms_received', array( $this, 'process' ), 10, 7 );
	}

	public function before_process_webhook_contact( $automation_id, $automation_key, $request_data ) {
		$hook  = 'bwfan_webhook_psgate_sms_received';
		$args  = array(
			'psgate_data'    => $request_data,
			'automation_key' => $automation_key,
			'automation_id'  => $automation_id
		);
		$group = 'psgate';

		if ( bwf_has_action_scheduled( $hook, $args, $group ) ) {
			return;
		}
		bwf_schedule_single_action( time(), $hook, $args, $group );

	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_psgate_phone() {
		if ( ! isset( $this->psgate_data['From'] ) || empty( $this->psgate_data['From'] ) ) {
			return false;
		}

		return $this->psgate_data['From'];
	}

	public function get_order_from_phone() {
		$phone = $this->get_psgate_phone();
		if ( false === $phone ) {
			return false;
		}

		$args   = array(
			'billing_phone' => $phone,
		);
		$orders = wc_get_orders( $args );
		if ( empty( $orders ) ) {
			return false;
		}

		return $orders[0];
	}

	public function get_email_event() {
		$order = $this->get_order_from_phone();

		return $order instanceof WC_Order ? $order->get_billing_email() : false;
	}

	public function get_user_id_event() {
		$order = $this->get_order_from_phone();

		return $order instanceof WC_Order ? $order->get_user_id() : false;
	}

	public function bwfan_webhook_admin_enqueue_assets() {
		$this->psgate_id = isset( $_GET['edit'] ) ? sanitize_text_field( $_GET['edit'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $this->psgate_id ) ) {
			$meta = BWFAN_Model_Automationmeta::get_meta( $this->psgate_id, 'event_meta' );
			if ( isset( $meta['bwfan_unique_key'] ) && ! empty( $meta['bwfan_unique_key'] ) ) {
				$this->localized_automation_key = $meta['bwfan_unique_key'];
			}
		}

		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'automation_id', $this->psgate_id );
		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'saved_localized_automation_key', $this->localized_automation_key );
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();

		if ( ! is_array( $global_data['psgate_data'] ) || empty( $global_data['psgate_data'] ) ) {
			?>
            <li>
                <strong><?php esc_html_e( 'No Data Available', 'autonami-automations-connectors' ); ?> </strong>
            </li>
			<?php
			return ob_get_clean();
		}

		$data_to_show = array(
			'From'        => 'From',
			'FromCountry' => 'Country',
			'Body'        => 'Message'
		);

		foreach ( $data_to_show as $key => $alias ) {
			if ( ! isset( $global_data['psgate_data'][ $key ] ) || empty( $global_data['psgate_data'][ $key ] ) ) {
				continue;
			}
			?>
            <li>
                <strong><?php esc_html_e( "$alias: " ); ?></strong>
				<?php esc_html_e( $global_data['psgate_data'][ $key ] ); ?>
            </li>
			<?php

		}

		return ob_get_clean();
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php esc_attr_e( $this->get_slug() ); ?>">
            <#
            var eventslug = '<?php esc_html_e( $this->get_slug() ); ?>';
            var eventData = bwfan_events_js_data[eventslug];
            var event_save_unique_key =eventData.saved_localized_automation_key;
            if(event_save_unique_key.length>0){
            eventData.localized_automation_key = event_save_unique_key
            }
            var webhook_url = '<?php esc_attr_e( home_url( '/' ) ); ?>wp-json/autonami/v1/psgate/webhook?psgate_id='+eventData.automation_id+'&psgate_key='+eventData.localized_automation_key;
            #>
            <div class="bwfan_mt15"></div>
            <label for="bwfan-webhook-url" class="bwfan-label-title"><?php esc_html_e( 'Url', 'autonami-automations-connectors' ); ?></label>
            <div class="bwfan-textarea-box">
                <textarea name="event_meta[bwfan_webhook_url]" class="bwfan-input-wrapper bwfan-webhook-url" id="bwfan-webhook-url" cols="45" rows="2" onclick="select();" readonly>{{webhook_url}}</textarea>
                <input type="hidden" name="event_meta[bwfan_unique_key]" id="bwfan-unique-key" value={{eventData.localized_automation_key}}>
            </div>
        </script>
		<?php
	}

	public function process( $psgate_data, $automation_key, $automation_id ) {
		$this->psgate_id      = $automation_id;
		$this->automation_key = $automation_key;
		$this->psgate_data    = $psgate_data;

		return $this->run_automations();
	}

	/**
	 * A controller function to run automation every time an appropriate event occurs
	 * usually called by the event class just after the event hook to load all automations and run.
	 * @return array|bool
	 */
	public function run_automations() {
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );
		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
			if ( $this->sync_start_time > 0 ) {
				/** Sync process */
				BWFAN_Core()->logger->log( 'Sync #' . $this->sync_id . '. No active automations found for Event ' . $this->get_slug(), 'sync' );

				return false;
			}
			BWFAN_Core()->logger->log( 'Async callback: No active automations found. Event - ' . $this->get_slug(), $this->log_type );

			return false;
		}

		$automation_actions = [];

		foreach ( $this->automations_arr as $automation_id => $automation_data ) {
			if ( $this->get_slug() !== $automation_data['event'] || 0 !== intval( $automation_data['requires_update'] ) ) {
				continue;
			}

			//check if the automation_key match with the post data
			$unique_key_matched    = ( isset( $automation_data['event_meta']['bwfan_unique_key'] ) && $this->automation_key === $automation_data['event_meta']['bwfan_unique_key'] );
			$automation_id_matched = ( absint( $automation_id ) === absint( $this->psgate_id ) );
			if ( $unique_key_matched && $automation_id_matched ) {
				$ran_actions = $this->handle_single_automation_run( $automation_data, $automation_id );
			}


			$automation_actions[ $automation_id ] = $ran_actions;
		}

		return $automation_actions;
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}

		$meta = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );

		if ( '' === $meta || ! is_array( $meta ) ) {
			return;
		}

		$data_to_send = $this->get_event_data();
		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                             = [];
		$data_to_send['global']['psgate_id']      = $this->psgate_id;
		$data_to_send['global']['automation_key'] = $this->automation_key;
		$data_to_send['global']['psgate_data']    = $this->psgate_data;
		$data_to_send['global']['phone']          = $this->get_psgate_phone();
		$data_to_send['global']['email']          = $this->get_email_event();

		return $data_to_send;
	}

	public function set_merge_tags_data( $task_meta ) {
		$merge_data                = [];
		$merge_data['psgate_data'] = $task_meta['global']['psgate_data'];
		$merge_data['phone']       = $task_meta['global']['phone'];
		$merge_data['email']       = $task_meta['global']['email'];
		BWFAN_Merge_Tag_Loader::set_data( $merge_data );
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
$saved_connectors = WFCO_Common::$connectors_saved_data;

if ( empty( $saved_connectors ) ) {
	WFCO_Common::get_connectors_data();
	$saved_connectors = WFCO_Common::$connectors_saved_data;
}

if ( array_key_exists( 'bwfco_psgate', $saved_connectors ) ) {
	return 'BWFAN_Psgate_Message_Received';
}