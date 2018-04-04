<?php
/*
Plugin Name: Post Content Updater
Plugin URI: https://github.com/rklymenko
Description: Update content for post
Version: 0.1
Author: slan
Author URI: https://github.com/rklymenko
*/

function update_post_content( $data ) {

	$add_title = ' myproject.com';
	$add_banner = '<div class="banner"><img src="' . get_template_directory_uri() . '/banner/1.png" alt="Banner" title="Banner" /></div>';
	$log_message = 'Post ' . $data['ID'] . ' was updated';

	$sub_title = stripslashes(substr($data['post_title'], -strlen(addslashes($add_title))));
	if( $sub_title != $add_title ){
    	$data['post_title'] .=  $add_title ;
	}	

	$sub_content = stripslashes(substr($data['post_content'], -strlen(addslashes($add_banner))));

	if( $sub_content != $add_banner ){
    	$data['post_content'] .=  $add_banner ;
	}

	error_log($log_message);

    return $data;
}
add_action( 'wp_insert_post_data', 'update_post_content', 10, 3 );