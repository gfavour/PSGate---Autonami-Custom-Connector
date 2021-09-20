<?php

class WFCO_Psgate_Send_SMS extends WFCO_Call {

	private static $instance = null;
	private $api_end_point = null;

	public function __construct() {
		$this->required_fields = array( 'username', 'password', 'psgate_no'); //, 'sms_body', 'phone' 
		$this->api_end_point   = 'http://login.betasms.com.ng/api/';
		//$this->api_end_point   = 'https://www.bulksmsnigeria.com/api/v1/sms/create';
	}

	/**
	 * @return WFCO_Psgate_Send_SMS|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/////////////////////////
	public function sendbyGet($allurl){
		$ch = curl_init();
		$headers = array('Content-Type: application/x-www-form-urlencoded');
		curl_setopt($ch, CURLOPT_URL, $allurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(trim(curl_exec($ch)),true);
		curl_close ($ch); //flush();
		return $result;
	}
	
				
	public function process() {
		$phone_numbers = trim( stripslashes( $this->data['phone'] ) );
		$phone_numbers = explode( ',', $phone_numbers );
		$this->data['sms_body'] = apply_filters( 'bwfan_modify_send_sms_body', $this->data['sms_body'], $this->data );
		//////////
		$rparams = [];
		foreach($phone_numbers as $phone){
			if (isset( $this->data['country_code']) && !empty($this->data['country_code'])){
				$rparams[] = Phone_Numberz::add_country_code( $phone, $this->data['country_code'] );
			}else{
				$rparams[] = $phone;
			}
		}
		///////////////
		if(count($rparams) > 0){
			$phone_nos = implode(",",$rparams);
			$allurl = $this->api_end_point.'?username='.urlencode($this->data['username']).'&password='.urlencode($this->data['password']).'&sender='.urlencode($this->data['psgate_no']).'&message='.urlencode($this->data['sms_body']).'&mobiles='.urlencode($phone_nos);
			$res = $this->sendbyGet($allurl);
			return true;
		}else{
			return false;
		}
		
	}
	///////////////////////////////
	
	public function process2() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}
		$media_urls = ! empty( $this->data['mediaUrl'] ) ? $this->data['mediaUrl'] : '';
		$url        = $this->api_end_point; //.$this->data['username'].'/Messages.json';
		$headers    = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			//'Authorization' => 'Basic ' . base64_encode( $this->data['username'] . ':' . $this->data['password'] ),
		);
		
		$phone_nos = trim( stripslashes( $this->data['phone'] ) );
		$phone_numbers = explode( ',', $phone_nos );

		$this->data['sms_body'] = apply_filters( 'bwfan_modify_send_sms_body', $this->data['sms_body'], $this->data );
		
		$req_params = array(
			'Body' => $this->data['sms_body'],
			'From' => $this->data['psgate_no'],
		);
		
		$req_params = array(
			'username' => $this->data['username'],
			'password' => $this->data['password'],
			'message' => $this->data['sms_body'],
			'sender' => $this->data['psgate_no'],
		);

		foreach ( $phone_numbers as $phone ) {
			$req_params['mobiles'] = $phone; //To

			/** User 2 digit country code passed */
			if ( isset( $this->data['country_code'] ) && ! empty( $this->data['country_code'] ) ) {
				$req_params['mobiles'] = Phone_Numberz::add_country_code( $phone, $this->data['country_code'] );
			}

			/** Filter hook to modify to mobile number per event */
			$req_params['mobiles'] = apply_filters( 'bwfan_modify_send_sms_to', $req_params['mobiles'], $this->data );

			if ( ! empty( $media_urls ) ) {
				$req_params['MediaUrl'] = $media_urls;

				$res = $this->make_wp_requests( $url, $req_params, $headers, BWF_CO::$GET );
				continue;
			}
			$res = $this->make_wp_requests( $url, $req_params, $headers, BWF_CO::$GET );
		}

		return $res;
	}


}

return 'WFCO_Psgate_Send_SMS';
