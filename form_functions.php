<?php
/*
Plugin Name: IFF Membership Form Functions
Description: Plugin for abstracting custom form functions
Author: Scott O'Malley
Version: 1.0
GitHub Plugin URI: https://github.com/thehandsomecoder/IFFMembershipLookupTool
GitHub Branch: master
*/
//------------------------------------------

add_filter( 'gform_validation_message_1', 'change_iff_renew_message', 10, 2 );

function change_iff_renew_message( $message, $form ) {
  return "<div class='validation_error'>" . esc_html__( "There was a problem with the data entered.", 'gravityforms' ) . ' ' . esc_html__( "The email and IFF number entered don't match our records", "gravityforms" ) . "</div>";
}

add_filter( 'gform_validation_1', 'validate_iff_input' );
function validate_iff_input( $validation_result ) {
    
    $form = $validation_result['form'];

    $validation_result['is_valid'] = true;    

    return $validation_result;
}


add_filter( 'gform_pre_submission_1', 'iff_renewal_form_pre_submission' );
function iff_renewal_form_pre_submission( $form ) {
    $_POST['input_3'] = strval(md5(uniqid(rand(), true)));
}


?>