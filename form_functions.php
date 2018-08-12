<?php
/*
Plugin Name: IFF Membership Form Functions
Description: Plugin for abstracting custom form functions
Author: Scott O'Malley
Version: 1.6.1
GitHub Plugin URI: https://github.com/TheHandsomeCoder/IFF-Form-Functions
GitHub Branch:     master
*/
//------------------------------------------

add_filter( 'gform_validation_message_22', 'change_iff_renew_message', 10, 2 );

function change_iff_renew_message($message, $form)
{
    return "<div class='validation_error'>" . esc_html__( "There was a problem with the data entered.", 'gravityforms' ) . ' ' . esc_html__( "The email and IFF number entered don't match our records", "gravityforms" ) . "</div>";
}

add_filter( 'gform_validation_22', 'form_functions_validate_iff_input' );
function form_functions_validate_iff_input($validation_result)
{
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

    $licenced2015 = GFAPI::get_entries(1, $search_criteria);
    $licenced2016 = GFAPI::get_entries(21, $search_criteria);
    $licenced2017 = GFAPI::get_entries(32, $search_criteria);


    $validation_result['is_valid'] = (
            count($licenced2015) == 1 ||
            count($licenced2016) == 1 ||
            count($licenced2017) == 1);

    return $validation_result;
}


add_filter( 'gform_pre_submission_22', 'iff_renewal_form_pre_submission' );
function iff_renewal_form_pre_submission($form)
{
    $_POST['input_3'] = substr(strval(md5(uniqid(rand(), true))),0,8);
}

add_filter( 'gform_after_submission_22', 'iff_renewal_form_post_submission' );
function iff_renewal_form_post_submission($form)
{

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

//===================================================================

add_filter('gform_pre_submission_61', 'populateIFFNumber');
function populateIFFNumber( $form ) {

  $iff_number = rgpost( "input_19" );
  debug_to_console($iff_number);

  if($iff_number == null) {
    debug_to_console("iff_number");

    $IFFLicenseNumber =  intval(get_option( "new_iff_licence_numbers"));

    $_POST['input_19'] = strval($IFFLicenseNumber)."IRL";

    $IFFLicenseNumber += 1;

    update_option("new_iff_licence_numbers", strval($IFFLicenseNumber));
  }
}

add_filter( 'gform_validation_61', 'form_61_validate_input' );
function form_61_validate_input( $validation_result ) {
  $form = $validation_result['form'];
  $current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' .   $form['id'] ) : 1;

  if($current_page == 1){
   form_61_reset_details();
  }
  else if ($current_page == 2) {
    $formID = 22;

    $email = rgpost( "input_37" );
    $iff_number = rgpost("input_36");
    $hash = rgpost("input_38");

    $search_criteria = array(
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
                'value' => $hash
            )
        )
    );

    $detailsFound = GFAPI::get_entries($formID, $search_criteria);

    $is_valid = (count($detailsFound) == 1 ? true : false);

    debug_to_console($is_valid);

    $validation_result['is_valid'] = $is_valid;

    if($is_valid) {

      $fencer = get_fencer_details($iff_number);
      debug_to_console('===================');
      debug_to_console(json_encode($fencer));
      debug_to_console('===================');

      $_POST['input_1_2'] = $fencer['1.2'];
      $_POST['input_1_3'] = $fencer['1.3'];
      $_POST['input_1_6'] = $fencer['1.6'];
      $_POST['input_19'] =  $fencer['19'];
    }
    else{
       foreach( $form['fields'] as &$field ) {
        if($field['id'] == 36){
          $field->failed_validation = true;
          $field->validation_message = "The details entered don't match our records, please check them and try again.";
        }
        else if ($field['id'] == 37){
          $field->failed_validation = true;
          $field->validation_message = "The details entered don't match our records, please check them and try again.";
        }
        else if ($field['id'] == 38){
          $field->failed_validation = true;
          $field->validation_message = "The details entered don't match our records, please check them and try again.";
        }
       }
    }

  }

  return $validation_result;
}

function get_fencer_details($iff_number){

    $membershipForm1 = 1;
    $membershipForm21 = 21;
    $membershipForm32 = 32;

    $search_criteria = array(
        'field_filters' => array(
            'mode' => 'all',
            array(
                'key'   => '19',
                'value' => $iff_number
            )
        )
    );

    $fencerQuery = GFAPI::get_entries($membershipForm32, $search_criteria);
    debug_to_console(json_encode($fencerQuery));
    if(count($fencerQuery) == 1) {
        return $fencerQuery[0];
    }

    $fencerQuery = GFAPI::get_entries($membershipForm21, $search_criteria);
    debug_to_console(json_encode($fencerQuery));
    if(count($fencerQuery) == 1) {
        return $fencerQuery[0];
    }

    $fencerQuery = GFAPI::get_entries($membershipForm1, $search_criteria);
    debug_to_console(json_encode($fencerQuery));
    if(count($fencerQuery) == 1){
        return $fencerQuery[0];
    }





}

function form_61_reset_details(){
    $_POST['input_1_2'] = '';
    $_POST['input_1_3'] = '';
    $_POST['input_1_6'] = '';
    $_POST['input_19'] = '';
    $_POST['input_37'] = '';
    $_POST['input_38'] = '';
    $_POST['input_36'] = '';
}


function debug_to_console( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}


// FIE/EFC Membership check.

add_filter( 'gform_validation_62', 'form_62_validate_input' );
function form_62_validate_input( $validation_result ) {
  $form = $validation_result['form'];
  $current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' .   $form['id'] ) : 1;
  $formID = 61;

  if($current_page == 1){
   $iff_number = rgpost("input_51");
   $search_criteria = array(
      'field_filters' => array(
            'mode' => 'all',
            array(
                'key' => '36',
                'value' => $iff_number
            )
        )
    );

    $detailsFound = GFAPI::get_entries($formID, $search_criteria);
    $is_valid = (count($detailsFound) == 1 ? true : false);
    debug_to_console($is_valid);
    $validation_result['is_valid'] = $is_valid;
    if(!$is_valid) {
        foreach( $form['fields'] as &$field ) {
            if($field['id'] == 61){
            $field->failed_validation = true;
            $field->validation_message = "We couldn't find your Fencing Ireland membership, do you have a current licence?";
            }
        }
    }
  }
  

  return $validation_result;
}