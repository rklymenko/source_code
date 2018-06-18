<?php
/*
Plugin Name: Know Your Customer
Plugin URI: http://cubydev.com
Description: Know Your Customer API
Version: 0.1
Author: slan
Author URI: http://cubydev.com
*/


class know_your_customer {

    function __construct() {
        add_action( 'admin_menu', array( $this, 'api_settings_page_menu' ) );
    }

    function api_settings_page_menu() {
        add_options_page(
            'Know Your Customer',
            'Know Your Customer',
            'manage_options',
            'know_your_customer',
            array(
                $this,
                'api_settings_page_content'
            )
        );
    }

    function  api_settings_page_content() {
        ?>
        <h1>Know Your Customer (API Settings)</h1>
        <?php
        if(!empty($_POST) && array_key_exists('kyc_api_key', $_POST)){
            if ( get_option( 'kyc_api_key' ) !== false ) {

                // The option already exists, so we just update it.
                update_option( 'kyc_api_key', $_POST['kyc_api_key'] );

            } else {
                add_option( 'kyc_api_key', $_POST['kyc_api_key'] );
            }
        }

        if(!empty($_POST) && array_key_exists('kyc_contact_form', $_POST)){
            if ( get_option( 'kyc_contact_form' ) !== false ) {

                // The option already exists, so we just update it.
                update_option( 'kyc_api_key', $_POST['kyc_contact_form'] );

            } else {
                add_option( 'kyc_contact_form', $_POST['kyc_contact_form'] );
            }
        }

        
        $kyc_api_key = get_option( 'kyc_api_key' );
        $kyc_contact_form = get_option( 'kyc_contact_form' );


        ?>
        <form method="POST">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">Api Key</th>
                <td><input type="text" name="kyc_api_key" value="<?php echo $kyc_api_key; ?>" style="width: 50%;"/></td>
            </tr>
            <tr>
                <th scope="row">Contact Form Id</th>
                <td><input type="text" name="kyc_contact_form" value="<?php echo $kyc_contact_form; ?>" style="width: 50%;"/></td>
            </tr>
            </tbody>
        </table>
        <input type="submit" value="Save" class="button button-primary button-large">
        </form>
        <?php
    }
}

new know_your_customer;


function kyc_get_api_data($form){
	$body = false;
    $form_id = intval(get_option( 'kyc_contact_form' ));
	if($form->id == $form_id){
		$post_data = array();
		$post_data['firstName'] = $_POST['first-name'];
		$post_data['lastName'] = $_POST['family-name'];
		$post_data['birthDate'] = $_POST['dob'];
		$post_data['city'] = $_POST['town-city'];

		$post_data = json_encode($post_data);
		$server_url = 'https://api-demo.knowyourcustomer.com/v2/individuals';
		$response = false;
		$ch = curl_init($server_url);

	    // curl_setopt($ch, CURLOPT_URL, $server_url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json",
	                                            "Accept: application/json",
	                                            "ApiKey: ".get_option( 'kyc_api_key' )));
	    // curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	    // curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	    // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	    // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    $response = curl_exec($ch);
	    if (curl_errno($ch)) {
	        // Error
	        var_dump(curl_errno($ch));
	        var_dump(curl_error($ch));
	        var_dump(curl_strerror(curl_errno($ch)));
	        $information = curl_getinfo($ch);
			var_dump($information);
	    } else {
	        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
	        curl_close($ch);
	    }

	    if($body){
	    	$response = json_decode($body);
            
	    	if($response->caseDetail && $response->caseDetail->details && $response->caseDetail->details->individual && $response->caseDetail->details->common){
	    		$individual = $response->caseDetail->details->individual;
                $common = $response->caseDetail->details->common;
                $customer_name = $individual->firstName . ' ' . $individual->lastName;
                $customer = array(
                  'post_title'    => wp_strip_all_tags( $customer_name ),
                  'post_content'  => '',
                  'post_type'   => 'kyccustomer',
                  'post_status'   => 'publish'
                );
                
                // Insert the post into the database
                $the_customer_id = wp_insert_post( $customer );
                __update_post_meta( $the_customer_id, 'caseCommonId', $common->caseCommonId );
                __update_post_meta( $the_customer_id, 'caseCustomerId', $common->caseCustomerId );
                __update_post_meta( $the_customer_id, 'typeString', $common->typeString );
                __update_post_meta( $the_customer_id, 'complete', $common->complete.'' );
                __update_post_meta( $the_customer_id, 'status', $common->status );
                __update_post_meta( $the_customer_id, 'caseIndividualId', $individual->caseIndividualId );
                __update_post_meta( $the_customer_id, 'firstName', $individual->firstName );
                __update_post_meta( $the_customer_id, 'lastName', $individual->lastName );
	    	}
	    }
	    //wp_die();
	}
}

add_action( 'wpcf7_before_send_mail', 'kyc_get_api_data' );

function __update_post_meta( $post_id, $field_name, $value = '' )
{
    if ( empty( $value ) )
    {
        delete_post_meta( $post_id, $field_name );
    }
    elseif ( ! get_post_meta( $post_id, $field_name ) )
    {
        add_post_meta( $post_id, $field_name, $value );
    }
    else
    {
        update_post_meta( $post_id, $field_name, $value );
    }
}


function kyccustomer_post_type() {

// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Customers', 'Post Type General Name', 'twentyfifteen' ),
        'singular_name'       => _x( 'Customer', 'Post Type Singular Name', 'twentyfifteen' ),
        'menu_name'           => __( 'KYC Customers', 'twentyfifteen' ),
        'parent_item_colon'   => __( 'Parent Customer', 'twentyfifteen' ),
        'all_items'           => __( 'All Customers', 'twentyfifteen' ),
        'view_item'           => __( 'View Customer', 'twentyfifteen' ),
        'add_new_item'        => __( 'Add New Customer', 'twentyfifteen' ),
        'add_new'             => __( 'Add New', 'twentyfifteen' ),
        'edit_item'           => __( 'Edit Customer', 'twentyfifteen' ),
        'update_item'         => __( 'Update Customer', 'twentyfifteen' ),
        'search_items'        => __( 'Search Customer', 'twentyfifteen' ),
        'not_found'           => __( 'Not Found', 'twentyfifteen' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentyfifteen' ),
    );

// Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'kyccustomer', 'twentyfifteen' ),
        'description'         => __( 'Customers list', 'lendgenius' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields', ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-admin-users',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
        'rewrite' => array('slug' => 'kyccustomer', 'with_front' => false),
    );

    // Registering  Custom Post Type(loans)
    register_post_type( 'kyccustomer', $args );

}

add_action( 'init', 'kyccustomer_post_type', 0 );