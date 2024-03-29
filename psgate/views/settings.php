<?php

$saved_data  = WFCO_Common::$connectors_saved_data;
$old_data    = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$username = isset( $old_data['username'] ) ? $old_data['username'] : '';
$password  = isset( $old_data['password'] ) ? $old_data['password'] : '';
$psgate_no   = isset( $old_data['psgate_no'] ) ? $old_data['psgate_no'] : '';
?>
<div class="wfco-form-group featured field-input">
    <label for="automation-name"><?php echo esc_html__( 'Enter Account Username', 'autonami-automations-connectors' ); ?></label>
    <div class="field-wrap">
        <div class="wrapper">
            <input type="text" name="username" placeholder="<?php echo esc_attr__( 'Enter Account Username', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $username ); ?>">
        </div>
    </div>
</div>
<div class="wfco-form-group featured field-input">
    <label for="automation-name"><?php echo esc_html__( 'Enter Account Password', 'autonami-automations-connectors' ); ?></label>
    <div class="field-wrap">
        <div class="wrapper">
            <input type="text" name="password" placeholder="<?php echo esc_attr__( 'Enter Account Password', 'autonami-automations-connectors' ); ?>" class="form-control" required value="<?php echo esc_attr__( $password ); ?>">
        </div>
    </div>
</div>

<div class="wfco-form-group featured field-input">
    <label for="automation-name"><?php echo esc_html__( 'Sender', 'autonami-automations-connectors' ); ?> (optional)</label>
    <div class="field-wrap">
        <div class="wrapper">
            <input type="text" name="psgate_no" placeholder="<?php echo esc_attr__( 'Sender', 'autonami-automations-connectors' ); ?>" class="form-control" value="<?php echo esc_attr__( $psgate_no ); ?>">
        </div>
    </div>
</div>

<div class="wfco-form-groups wfco_form_submit">
	<?php
	if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
		?>
        <input type="hidden" name="edit_nonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
        <input type="hidden" name="id" value="<?php echo esc_attr__( $old_data['id'] ); ?>"/>
        <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
        <input type="submit" class="wfco_update_btn_style wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Update', 'autonami-automations-connectors' ); ?>">
	<?php } else { ?>
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector' ) ); ?>">
        <input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
        <input type="submit" class="wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Save', 'autonami-automations-connectors' ); ?>">
	<?php } ?>
</div>
<div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
