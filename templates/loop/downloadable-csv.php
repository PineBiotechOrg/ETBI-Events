<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! isset($_REQUEST['etbi_participant_csv']) || empty($_REQUEST['etbi_participant_csv']) ) {
	wp_safe_redirect( get_permalink( get_the_ID() ) );
	exit;
}

if( ! isset($_REQUEST['_etbi_download_csv']) || empty($_REQUEST['_etbi_download_csv']) ) {
	wp_safe_redirect( get_permalink( get_the_ID() ) );
	exit;
}

// if( ! wp_verify_nonce($_REQUEST['_etbi_download_csv'], '_etbi_download_csv') ) {
// 	wp_safe_redirect( get_permalink( get_the_ID() ) );
// 	exit;
// }

if( ! is_user_logged_in() ) {
	wp_safe_redirect( get_permalink( get_the_ID() ) );
	exit;
}

if( ! current_user_can('administrator') ) {
	wp_safe_redirect( get_permalink( get_the_ID() ) );
	exit;
}

$event_title = sanitize_title( $_REQUEST['etbi_participant_csv'], 'participants' );
$download_date = date('d-M-Y');
$bookings = array();
$args = array(
	'post_type'     => 'event_auth_book',
	'posts_per_page' => - 1,
	'order'         => 'DESC',
	'post_status'	=> 'ea-completed',
	'meta_query'    => array(
		array(
			'key'   => 'ea_booking_event_id',
			'value' => get_the_ID()
		),
	),
);
$query = new WP_Query( $args );

header( "Expires: 0" );
header( "cache-Control: no-cache, no-store, must-revalidate" );
header( "Pragma: no-cache" );
header("content-type: text/csv");
header("content-disposition: attachment; filename={$event_title}-{$download_date}.csv");

function wpems_get_user_email_for_booking( $booking ) {

	if( $booking->user_email ) {
		return $booking->user_email;
	}

	return '(No email)';

}

function wpems_get_user_name_for_booking( $booking ) {

	if( $booking->user_name ) {
		return $booking->user_name;
	}

	return '(No name)';

}

function wpems_get_organization_for_booking( $booking ) {
	if( $booking->user_organization ) {
		return $booking->user_organization;
	}

	return '(No organization)';
}

function wpems_get_location_for_booking( $booking ) {
	if( $booking->user_location ) {
		return $booking->user_location;
	}
	return '(No location)';
}

function wpems_get_degree_for_booking( $booking ) {
	if( $booking->user_degree ) {
		return $booking->user_degree;
	}
	return '(No degree)';
}

function output_csv( $query ) {
	$output = fopen("php://output", "wb");

	fputcsv($output, array('# Ticket ID', 'Email', 'Name', 'Organization', 'Location', 'Degree', 'QTY' ) );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$booking = WPEMS_Booking::instance( $post->ID );
			$user_id = $booking->user_id;
			$ticket_id = wpems_format_ID( $post->ID );
			$user_email = wpems_get_user_email_for_booking( $booking );
			$user_name = wpems_get_user_name_for_booking( $booking );
			$user_organization = wpems_get_organization_for_booking( $booking );
			$user_location = wpems_get_location_for_booking( $booking );
			$user_degree = wpems_get_degree_for_booking( $booking );
			$qty = $booking->qty;
			fputcsv( $output, array( $ticket_id, $user_email, $user_name, $user_organization, $user_location, $user_degree, $qty ) );
		}
	}

	fclose($output);
}

output_csv( $query );

exit;