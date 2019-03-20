<?php
/**
 * WP Events Manager Ajax class
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
 * Ajax Process
 */
class WPEMS_Ajax {

	public function __construct() {
		// actions with
		// key is action ajax: wp_ajax_{action}
		// value is allow ajax nopriv: wp_ajax_nopriv_{action}
		$actions = array(
			'event_remove_notice' => true,
			'event_apply_coupon'  => true,
			'event_auth_register' => true,
			'event_login_action'  => true,
			'load_form_register'  => true,
			'register_all_events' => false
		);

		foreach ( $actions as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( $this, $action ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( $this, $action ) );
			} else {
				add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'must_login' ) );
			}
		}
	}

	/**
	 * Remove admin notice
	 */
	public function event_remove_notice() {

		if ( is_multisite() ) {
			update_site_option( 'thimpress_events_show_remove_event_auth_notice', 1 );
		} else {
			update_option( 'thimpress_events_show_remove_event_auth_notice', 1 );
		}
		wp_send_json( array(
			'status'  => true,
			'message' => __( 'Remove admin notice successful', 'wp-events-manager' )
		) );
	}


	/**
	 * load form register
	 * @return html login form if user not logged in || @return html register event form
	 */
	public function load_form_register() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'event-auth-register-nonce' ) ) {
			return;
		}

		$event_id = ! empty( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

		if ( ! $event_id ) {
			wpems_add_notice( 'error', __( 'Event not found.', 'wp-events-manager' ) );
			wpems_print_notices();
			die();
		} else if ( ! is_user_logged_in() ) {
			wpems_print_notices( 'error', __( 'You must login before register ', 'wp-events-manager' ) . sprintf( ' <strong>%s</strong>', get_the_title( $event_id ) ) );
			die();
		} else {
			$event           = new WPEMS_Event( $event_id );
			$registered_time = $event->booked_quantity( get_current_user_id() );
			ob_start();
			if ( get_post_meta( $event_id, 'tp_event_status', true ) === 'expired' ) {
				wpems_print_notices( 'error', sprintf( '%s %s', get_the_title( $event_id ), __( 'has been expired', 'wp-events-manager' ) ) );
			} else if ( $registered_time && wpems_get_option( 'email_register_times' ) === 'once' && $event->is_free() ) {
				wpems_print_notices( 'error', __( 'You have registered this event before', 'wp-events-manager' ) );
			} else if ( ! $event->get_slot_available() ) {
				wpems_print_notices( 'error', __( 'The event is full, the registration is closed', 'wp-events-manager' ) );
			} else {
				wpems_get_template( 'loop/booking-form.php', array( 'event_id' => $event_id ) );
			}
			echo ob_get_clean();
			die();
		}
	}

	/**
	 * Login Ajax
	 */
	public function event_login_action() {
		WPEMS_User_Process::process_login();
		die();
	}

	public function event_apply_coupon() {

		try {

			// sanitize, validate data
			if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
				throw new Exception( __( 'Invalid request', 'wp-events-manager' ) );
			}

			if ( ! isset( $_POST['action'] ) || ! check_ajax_referer( 'event_apply_coupon_nonce', 'event_apply_coupon_nonce' ) ) {
				throw new Exception( __( 'Invalid request', 'wp-events-manager' ) );
			}


			$coupon_code = false;
			if( ! isset( $_POST['coupon_code'] ) || empty( $_POST['coupon_code'] ) ) {
				throw new Exception( __( 'No coupon applied', 'wp-events-manager' ) );
			} else {
				$coupon_code 	= strtolower( sanitize_text_field( $_POST['coupon_code'] ) );
			}

			$coupons = wpems_get_coupon_codes();
			if( ! array_key_exists( $coupon_code, $coupons ) ) {
				throw new Exception( __( 'Invalid coupon.', 'wp-events-manager' ) );
			} else {
				$applied_coupon = $coupons[ $coupon_code ];
			}

			
			$event_id = false;
			if ( ! isset( $_POST['event_id'] ) || ! is_numeric( $_POST['event_id'] ) ) {
				throw new Exception( __( 'Invalid event request', 'wp-events-manager' ) );
			} else {
				$event_id = absint( sanitize_text_field( $_POST['event_id'] ) );
			}

			$qty = 0;
			if ( ! isset( $_POST['qty'] ) || ! is_numeric( $_POST['qty'] ) ) {
				throw new Exception( __( 'Quantity must be an integer', 'wp-events-manager' ) );
			} else {
				$qty = absint( sanitize_text_field( $_POST['qty'] ) );
			}

			// End sanitize, validate data
			// load booking module
			$booking = WPEMS_Booking::instance();
			$event   = WPEMS_Event::instance( $event_id );

			if ( $event->is_free() ) {
				throw new Exception( __( 'This event is free! Why do you need a coupon?', 'wp-events-manager' ) );
			}

			$user       = wp_get_current_user();
			$registered = $event->booked_quantity( $user->ID );

			$discount_percentage = (float) $applied_coupon['discount'];
			$formatted_discount_percentage = $discount_percentage * 100 . '%';
			$original_price = (float) $event->get_price();
			$formatted_original_price = wpems_format_price( $original_price );
			$discounted_price = (float) $original_price * $discount_percentage;
			$discounted_price = $original_price - $discounted_price;
			$formatted_discounted_price = wpems_format_price( $discounted_price );

			if( $discounted_price < $original_price || $discounted_price == 0 ) {

				WPEMS()->_session->set( 'coupon', $coupon_code );

				wp_send_json( array(
					'status'  => true,
					'discount_percentage'	=> $discount_percentage,
					'formatted_discount_percentage' => $formatted_discount_percentage,
					'original_price'	=> $original_price,
					'formatted_original_price'	=> $formatted_original_price,
					'discounted_price'	=> $discounted_price,
					'formatted_discounted_price' => $formatted_discounted_price,
					'message' => __( $formatted_discount_percentage . ' savings', 'wp-events-manager' )
				) );

			}

		} catch ( Exception $e ) {

			if ( $e ) {
				wpems_add_notice( 'error', $e->getMessage() );
			}

		}

		wpems_print_notices();
		$message = ob_get_clean();
		// allow hook
		wp_send_json( array(
			'status'  => false,
			'message' => $message
		) );
		die();
	}

	public function register_all_events() {

		try {

			// sanitize, validate data
			if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
				throw new Exception( __( 'Invalid request', 'wp-events-manager' ) );
			}

			if( ! is_user_logged_in() ) {
				throw new Exception( __( 'Must be looged in to register to all events.', 'wp-events-manager' ) );

			}

			if( ! isset( $_POST['action'] ) || ! check_ajax_referer( 'register_all_events_nonce', 'register_all_events_nonce' ) ) {
				throw new Exception(  __( 'Invalid request', 'wp-events-manager' )  );
			}

			$user = wp_get_current_user();
			$user_id = $user->ID;
			$user_name = $user->user_login;
			$user_email = $user->user_email;
			$user_firstname = $user->user_firstname;
			$user_lastname = $user->user_lastname;
			$user_displayname = $user->user_display_name;
			$group_events_array = array();
			$event_ids = array();

			if( ! empty( $user_firstname ) && ! empty( $user_lastname ) ) {

				$user_name = $user_firstname . ' ' . $user_lastname;

			} else if( ! empty( $user_displayname ) ) {

				$user_name = $user_displayname;

			}

			if ( is_buddypress() && bp_is_groups_component() ) {

				$group_slug = bp_get_group_slug();
				$group_id = bp_get_current_group_id();

				$group_events_array = (array) maybe_unserialize( groups_get_groupmeta( $group_id, '_etbi_group_event_ids', true ) );

				$event_ids = wpems_get_group_event_ids( $group_events_array );
			}

			if( ! empty( $event_ids ) ) {

				foreach ( $event_ids as $key => $event_id ) {
					
					// End sanitize, validate data
					// load booking module
					$booking = WPEMS_Booking::instance();
					$event   = WPEMS_Event::instance( $event_id );
					$registered = $event->booked_quantity( $user_id );

					if ( $registered != 0 ) { // If the user has already registered to the event, skip it
						continue;
					}

					$program_id = wpems_get_program_id_from_child_event( $event_id );

					if( ! wpems_user_has_paid_for_program( $user_id, $program_id ) ) {

						error_log('USER HAS NOT PAID FOR PROGRAM');

						continue;

					}

					error_log( 'USER HAS PAID FOR PROGRAM' );

					$price = 0;
					$qty = 1;
					$payment = 'stripe';
					$location = '';
					$organization = '';
					$degree = '';
					$name = $user_name;
					$email = $user_email;
					$coupon_code = '';

					// create new book return $booking_id if success and WP Error if fail
					$args = apply_filters( 'tp_event_create_logged_in_booking_args', array(
						'event_id'   		=> $event_id,
						'qty'        		=> $price,
						'price'      		=> (float) $price * $qty,
						'payment_id' 		=> $payment,
						'currency'   		=> wpems_get_currency(),
						'user_location'		=> $location,
						'user_organization'	=> $organization,
						'user_degree'		=> $degree,
						'user_name'			=> $name,
						'user_email'		=> $email,
						'coupon_code'		=> $coupon_code
					) );

					$booking_id = $booking->create_booking( $args, $args['payment_id'] );

					error_log( json_encode( $args ) );

					if ( is_wp_error( $booking_id ) ) {

							throw new Exception( $booking_id->get_error_message() );

					} else {

						if ( $args['price'] == 0 ) {

							// update booking status
							$book = WPEMS_Booking::instance( $booking_id );
							$book->update_status();

							$event_id = $book->event_id;
							$event_url = get_permalink( $event_id );
							$event_title = get_the_title( $event_id );
							$booking_qty =  $book->qty;
							$success_message = sprintf( esc_html( _n( 'You have successfully registered for %1$d ticket for "%2$s". Check your email', 'You have successfully registered for %1$d tickets for "%2$s". Check your email.', $booking_qty, 'wp-events-manager' ) ), $booking_qty, $event_title );

							$result = array(
								'status'   => true,
								'message' => $success_message,
								'event' =>  'purchased',
								'url' => $event_url
							);

							if( is_user_logged_in() ) {
								$result['account_url'] = wpems_account_url();
							}
							
							wp_send_json( $result );
						}

					}

				}

			}

		} catch ( Exception $e ) {

			if ( $e ) {

				wpems_add_notice( 'error', $e->getMessage() );

			}

		}

		wpems_print_notices();
		$message = ob_get_clean();
		// allow hook
		wp_send_json( array(
			'status'  => false,
			'message' => $message
		) );
		die();

	} 

	// register event
	public function event_auth_register() {
		try {
			// sanitize, validate data
			if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
				throw new Exception( __( 'Invalid request', 'wp-events-manager' ) );
			}

			if( is_user_logged_in() ) {
				if ( ! isset( $_POST['action'] ) || ! check_ajax_referer( 'event_auth_register_nonce', 'event_auth_register_nonce' ) ) {
					throw new Exception( __( 'Invalid request', 'wp-events-manager' ) );
				}

			}


			$event_id = false;
			if ( ! isset( $_POST['event_id'] ) || ! is_numeric( $_POST['event_id'] ) ) {
				throw new Exception( __( 'Invalid event request', 'wp-events-manager' ) );
			} else {
				$event_id = absint( sanitize_text_field( $_POST['event_id'] ) );
			}

			$qty = 0;
			if ( ! isset( $_POST['qty'] ) || ! is_numeric( $_POST['qty'] ) ) {
				throw new Exception( __( 'Quantity must be a number', 'wp-events-manager' ) );
			} else {
				$qty = absint( sanitize_text_field( $_POST['qty'] ) );
			}


			// End sanitize, validate data
			// load booking module
			$booking = WPEMS_Booking::instance();
			$event   = WPEMS_Event::instance( $event_id );
			
			$name = null;
			if( ! isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
				throw new Exception( __( 'You must supply a name.', 'wp-events-manager' ) );
			} else {
				$name = sanitize_text_field( $_POST['name'] );
			}

			$email = null;
			if( ! isset( $_POST['email'] ) || empty( $_POST['email'] ) ) {
				throw new Exception( __( 'You must supply an email.', 'wp-events-manager' ) );
			} else if( ! is_email( $_POST['email'] ) ) {
				throw new Exception( __( 'You must supply a valid email.', 'wp-events-manager' ) );
			} else {
				$email = sanitize_text_field( $_POST['email'] );
			}

			$location = null;
			if( isset( $_POST['location'] ) && ! empty( $_POST['location'] ) ) {
				$location = sanitize_text_field( $_POST['location'] );
			}

			$organization = null;
			if( isset( $_POST['organization'] ) && ! empty( $_POST['organization'] ) ) {
				$organization = sanitize_text_field( $_POST['organization'] );
			}

			$degree = null;
			if( isset( $_POST['degree'] ) && ! empty( $_POST['degree'] ) ) {
				$degree = sanitize_text_field( $_POST['degree'] );
			}


			if( is_user_logged_in() ) {
				$user       = wp_get_current_user();
				$registered = $event->booked_quantity( $user->ID );
			} else {
				$registered = $event->booked_quantity( $email );
			}

			if ( $registered != 0 && wpems_get_option( 'email_register_times', 'once' ) === 'once' ) {
				throw new Exception( __( 'You are registered to this event.', 'wp-events-manager' ) );
			}	

			// if( is_user_logged_in() ) {
			// 	$user       = wp_get_current_user();
			// 	$registered = $event->booked_quantity( $user->ID );
			// }


			$payment_methods = wpems_payment_gateways();

			$payment = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : false;

			$coupon_code = null;
			$price = $event->get_price();
			if( isset( $_POST['coupon_code'] ) && ! empty( $_POST['coupon_code'] ) ) {
				$coupon_code 	= strtolower( sanitize_text_field( $_POST['coupon_code'] ) );
				$price = wpems_apply_coupon_code( $price, $coupon_code );
			}

			$is_free_for_user = false;
			$is_group_member = false;
			$user_bought_program = false;
			$group_parent = get_post_meta( $event_id, 'tp_event_group_parent' );
			$program = get_post_meta( $event_id, 'tp_event_program', true );
			$is_program = ( $program == 'true' ) ? true : false;

			if( function_exists('bp_is_active') && bp_is_active('groups') ) {

			    if( is_user_logged_in() ) {

			        $current_user_id = get_current_user_id();

			        foreach ( $group_parent as $key => $group_id ) {
			        
			            $is_group_member = (bool) groups_is_user_member( $current_user_id, $group_id );

			        }

			    }

			}

			if( ! $is_program ) {

			    $program_parent = get_post_meta( $event_id, 'tp_event_group_parent' );

			    if( is_user_logged_in() ) {

			        $current_user_id = get_current_user_id();

			        foreach ( $program_parent as $key => $program_id ) {

			            $program = WPEMS_Event::instance( $program_id );
			            
			            if( $program->booked_quantity( $current_user_id ) != 0 ) {

			                $user_bought_program = true;

			            }

			        }                

			    }

			}

			$is_free_for_user = ( $is_group_member || $user_bought_program ) ? true : false;

			if( $is_free_for_user ) {

				$price = 0;

			}

			

			if( is_user_logged_in() ) {
				// create new book return $booking_id if success and WP Error if fail
				$args = apply_filters( 'tp_event_create_logged_in_booking_args', array(
					'event_id'   		=> $event_id,
					'qty'        		=> $qty,
					'price'      		=> (float) $price * $qty,
					'payment_id' 		=> $payment,
					'currency'   		=> wpems_get_currency(),
					'user_location'		=> $location,
					'user_organization'	=> $organization,
					'user_degree'		=> $degree,
					'user_name'			=> $name,
					'user_email'		=> $email,
					'coupon_code'		=> $coupon_code
				) );

			} else {
				// create new book return $booking_id if success and WP Error if fail
				$args = apply_filters( 'tp_event_create_logged_out_booking_args', array(
					'event_id'   		=> $event_id,
					'qty'        		=> 1,
					'price'      		=> (float) $price * 1,
					'payment_id' 		=> $payment,
					'currency'   		=> wpems_get_currency(),
					'user_location'		=> $location,
					'user_organization'	=> $organization,
					'user_degree'		=> $degree,
					'user_name'			=> $name,
					'user_email'		=> $email,
					'coupon_code'		=> null
				) );
			}


			$payment = ! empty( $payment_methods[ $payment ] ) ? $payment_methods[ $payment ] : false;

			$return = array();

			if ( $args['price'] > 0 && $payment && ! $payment->is_available() ) {
				throw new Exception( sprintf( '%s %s', get_title(), __( 'is not ready. Please contact administrator to setup payment gateways', 'wp-events-manager' ) ) );
			}

			if ( $payment && $payment->id == 'woo_payment' ) {

				do_action( 'tp_event_register_event_action', $args );
				$return = $payment->process( $event_id );
				wp_send_json( $return );

			} else {

				$booking_id = $booking->create_booking( $args, $args['payment_id'] );
				// create booking result
				if ( is_wp_error( $booking_id ) ) {
					throw new Exception( $booking_id->get_error_message() );
				} else {
					if ( $args['price'] == 0 ) {
						// update booking status
						$book = WPEMS_Booking::instance( $booking_id );
						$book->update_status();

						// user booking
						if( is_user_logged_in() ) {
							$user = get_userdata( $book->user_id );
							$user_email = $user->user_email;
						} else {
							$user_email = $book->user_email;
						}

						$event_id = $book->event_id;
						$event_url = get_permalink( $event_id );
						$event_title = get_the_title( $event_id );
						$booking_qty =  $book->qty;
						$success_message = sprintf( esc_html( _n( 'You have successfully registered for %1$d ticket for "%2$s". Check your email', 'You have successfully registered for %1$d tickets for "%2$s". Check your email.', $booking_qty, 'wp-events-manager' ) ), $booking_qty, $event_title );

						$result = array(
							'status'   => true,
							'message' => $success_message,
							'event' =>  'purchased',
							'url' => $event_url
						);

						if( is_user_logged_in() ) {
							$result['account_url'] = wpems_account_url();
						}
						
						wp_send_json( $result );
					} else if ( $payment ) {

						$return = $payment->process( $booking_id );
						if ( isset( $return['status'] ) && $return['status'] === false ) {
							wp_delete_post( $booking_id );
						}
						wp_send_json( $return );
					} else {
						wp_send_json( array(
							'status'  => false,
							'message' => __( 'Payment method is not available', 'wp-events-manager' )
						) );
					}
				}
			}

		} catch ( Exception $e ) {
			if ( $e ) {
				wpems_add_notice( 'error', $e->getMessage() );
			}
		}
		wpems_print_notices();
		$message = ob_get_clean();
		// allow hook
		wp_send_json( array(
			'status'  => false,
			'message' => $message
		) );
		die();
	}

	// ajax nopriv: user is not signin
	public function must_login() {
		wp_send_json( array(
			'status'  => false,
			'message' => sprintf( __( 'You Must <a href="%s">Login</a>', 'wp-events-manager' ), tp_event_login_url() )
		) );
		die();
	}

}

// initialize ajax class process
new WPEMS_Ajax();
