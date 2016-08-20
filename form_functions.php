<?php
/*
Plugin Name: IFF Membership Form Functions
Description: Plugin for abstracting custom form functions
Author: Scott O'Malley
Version: 1.1
GitHub Plugin URI: https://github.com/TheHandsomeCoder/IFF-Form-Functions
GitHub Branch:     master
*/
//------------------------------------------

add_filter( 'gform_validation_message_22', 'change_iff_renew_message', 10, 2 );

function change_iff_renew_message( $message, $form ) {
  return "<div class='validation_error'>" . esc_html__( "There was a problem with the data entered.", 'gravityforms' ) . ' ' . esc_html__( "The email and IFF number entered don't match our records", "gravityforms" ) . "</div>";
}

add_filter( 'gform_validation_22', 'form_functions_validate_iff_input' );
function form_functions_validate_iff_input( $validation_result ) {
	$formID = 1;
   
    $email = rgpost( "input_1" );
    $iff_number = rgpost("input_2");  

    $search_criteria = array(
    	'field_filters' => array(
            'mode' => 'all',            
            array(
                'key' => '5',
                'value' => $email
            ),
            array(
                'key' => '19',
                'value' => $iff_number
          	)
        )
    );
        
    $detailsFound = GFAPI::get_entries($formID, $search_criteria);

    $validation_result['is_valid'] = (count($detailsFound) == 1 ? true : false);        
  
    return $validation_result;
}


add_filter( 'gform_pre_submission_22', 'iff_renewal_form_pre_submission' );
function iff_renewal_form_pre_submission( $form ) {
    $_POST['input_3'] = strval(md5(uniqid(rand(), true)));
}

add_filter( 'gform_after_submission_22', 'iff_renewal_form_post_submission' );
function iff_renewal_form_post_submission( $form ) {

	$formID = 22;
   
    $email = rgpost( "input_1" );
    $iff_number = rgpost("input_2");  
    $hash = rgpost("input_3");  

    $search_criteria = array(
    	'status' => 'active',
        'field_filters' => array(
            'mode' => 'all',            
            array(
                'key' => '1',
                'value' => $email
            ),
            array(
                'key' => '2',
                'value' => $iff_number
          	),
          	array(
                'key' => '3',
                'operator' => 'isnot',
                'value' => $hash
          	)
        )
    );
        
    $data = GFAPI::get_entries($formID, $search_criteria);

    foreach ($data as $value) {
    	GFAPI::delete_entry($value['id']);
    }

}

?>