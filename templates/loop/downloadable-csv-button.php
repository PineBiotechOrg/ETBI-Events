<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( is_user_logged_in() && current_user_can( 'administrator' ) ) {
	$event = new WPEMS_Event( get_the_ID() );
	$event_slug = get_post_field( 'post_name' );
	$event_slug .= '-participants';
	$downloadable_csv_link = wp_nonce_url( add_query_arg( 'etbi_participant_csv', $event_slug, get_the_permalink() ), '_etbi_download_event' . get_the_ID() . '_csv', '_etbi_download_csv' ); 

	?>
		<a href="<?php echo $downloadable_csv_link; ?>" target="_blank" class="download-csv-btn"><i class="fa fa-download"></i>  Participant CSV</a>

	<?php
}