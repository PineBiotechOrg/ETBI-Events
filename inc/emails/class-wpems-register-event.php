<?php
/**
 * WP Events Manager Register Event Mail class
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Class
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

/**
 *
 */
class WPEMS_Email_Register_Event {

	public function __construct() {

		add_action( 'tp_event_updated_status', array( $this, 'email_register' ), 10, 3 );
	}

	// send email
	public function email_register( $booking_id, $old_status, $status ) {

		if ( $old_status === $status ) {
			return;
		}

		if ( ! $booking_id ) {
			throw new Exception( sprintf( __( 'Error %s booking ID', 'wp-events-manager' ), $booking_id ) );
		}

		if ( wpems_get_option( 'email_enable', 'yes' ) === 'no' ) {
			return;
		}

		$booking = WPEMS_Booking::instance( $booking_id );

		if ( $booking ) {
			$user_id = $booking->user_id;
			$event = WPEMS_Event::instance( $booking->event_id );
			if ( wpems_get_option( 'allow_guest_checkout' ) === 'no' && ! $event->is_free() && ! $user_id ) {
				throw new Exception( __( 'User does not exist!', 'wp-events-manager' ) );
				die();
			} else if( wpems_get_option( 'allow_guest_checkout' ) === 'yes' && ! $event->is_free() && ! $user_id ) {
				$user = array(

					'user_name' 	=> $booking->user_name,
					'user_email'	=> $booking->user_email,
					'user_location'	=> $booking->user_location,
					'user_degree'	=> $booking->user_degree
				);

			} else if( $event->is_free() && ! $user_id ) {
				$user = array(

					'user_name' 	=> $booking->user_name,
					'user_email'	=> $booking->user_email,
					'user_location'	=> $booking->user_location,
					'user_degree'	=> $booking->user_degree
				);
			} else if( ( $event->is_free() || ! $event->is_free() ) && $user_id ) {

				$user = get_userdata( $user_id );

				$user = array(

					'user_name' 	=> $user->data->display_name,
					'user_email'	=> $user->data->user_email,
					'user_location'	=> $booking->user_location,
					'user_degree'	=> $booking->user_degree
				);		

			}
			

			$email_subject = wpems_get_option( 'email_subject' ) ? wpems_get_option( 'email_subject' ) : __( 'Register event', 'wp-events-manager' );

			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			// set mail from email
			add_filter( 'wp_mail_from', array( $this, 'email_from' ) );
			// set mail from name
			add_filter( 'wp_mail_from_name', array( $this, 'from_name' ) );

			if ( $user && $to = $user['user_email'] ) {


				$email_user_content  = wpems_get_template_content( 'emails/register-event.php', array(
					'booking' => $booking,
					'user'    => $user
				) );
				$email_admin_content = wpems_get_template_content( 'emails/register-admin-event.php', array(
					'booking' => $booking,
					'user'    => $user
				) );

				wp_mail( get_option( 'admin_email' ), $email_subject, stripslashes( $email_admin_content ), $headers );

				return wp_mail( $to, $email_subject, stripslashes( $email_user_content ), $headers );
			}
		}
	}

	// set from email
	public function email_from( $email ) {
		if ( $email = wpems_get_option( 'admin_email', get_option( 'admin_email' ) ) ) {
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				return $email;
			}
		}

		return $email;
	}

	// set from name
	public function from_name( $name ) {
		if ( $name = wpems_get_option( 'email_from_name' ) ) {
			return $name;
		}

		return $name;
	}

}

new WPEMS_Email_Register_Event();
