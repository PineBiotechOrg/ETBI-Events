<?php
/**
 * WP Events Manager Admin Metabox Event class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class WPEMS_Admin_Metabox_Event {

	public static function save( $post_id, $posted ) {
		if ( empty( $posted ) ) {
			return;
		}

		$program = true;

		if( ! array_key_exists( 'tp_event_program', $posted ) ) {

			$posted['tp_event_program'] = 'false';
			$program = false;

			$old_event_ids_array = maybe_unserialize( get_post_meta( $post_id, 'tp_event_event_ids', true ) );
			//Remove associations from old events
			foreach ($old_event_ids_array as $key => $old_event_id) {
				delete_post_meta( $old_event_id, '_tp_event_program_parent', $post_id );
			}

		}

		if( array_key_exists('tp_event_event_ids', $posted) ) {

			$old_event_ids_array = maybe_unserialize( get_post_meta( $post_id, 'tp_event_event_ids', true ) );
			$new_event_ids_array =  explode( ',', $posted['tp_event_event_ids'] );

			//Remove associations from old events
			foreach ($old_event_ids_array as $key => $old_event_id) {
				delete_post_meta( $old_event_id, '_tp_event_program_parent', $post_id );
			}

			//Add new associations for new events
			foreach ( $new_event_ids_array as $key => $new_event_id ) {
				update_post_meta( $new_event_id, '_tp_event_program_parent', $post_id );
			}

			$event_ids = maybe_serialize( $new_event_ids_array );
			$posted['tp_event_event_ids'] = $event_ids;

		}

		foreach ( $posted as $name => $value ) {
			if ( strpos( $name, 'tp_event_' ) !== 0 ) {
				continue;
			}

			update_post_meta( $post_id, $name, $value );
		}
		// Start
		$start = ! empty( $_POST['tp_event_date_start'] ) ? sanitize_text_field( $_POST['tp_event_date_start'] ) : '';
		$start .= $start && ! empty( $_POST['tp_event_time_start'] ) ? ' ' . sanitize_text_field( $_POST['tp_event_time_start'] ) : '';

		// End
		$end = ! empty( $_POST['tp_event_date_end'] ) ? sanitize_text_field( $_POST['tp_event_date_end'] ) : '';
		$end .= $end && ! empty( $_POST['tp_event_time_end'] ) ? ' ' . sanitize_text_field( $_POST['tp_event_time_end'] ) : '';

		if ( ( $start && ! $end ) || ( strtotime( $start ) >= strtotime( $end ) ) ) {
			WPEMS_Admin_Metaboxes::add_error( __( 'Please make sure event time is validate', 'wp-events-manager' ) );
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
		}

		$event_start = strtotime( $start );
		$event_end   = strtotime( $end );

		$time        = strtotime( current_time( 'Y-m-d H:i' ) );
		$offset_time = get_option( 'gmt_offset' ) * 60 * 60;

		$status = 'expired';
		if ( $event_start && $event_end ) {
			if ( $event_start > $time ) {
				$status = 'upcoming';
			} else if ( $event_start <= $time && $time < $event_end ) {
				$status = 'happening';
			} else if ( $time >= $event_end ) {
				$status = 'expired';
			}

			wp_schedule_single_event( $event_start - $offset_time, 'tp_event_schedule_status', array(
				$post_id,
				'happening'
			) );
			wp_schedule_single_event( $event_end - $offset_time, 'tp_event_schedule_status', array(
				$post_id,
				'expired'
			) );
		}

		update_post_meta( $post_id, 'tp_event_status', $status );

	}

	public static function render() {
		require_once( WPEMS_INC . 'admin/views/metaboxes/event-settings.php' );
	}

}
