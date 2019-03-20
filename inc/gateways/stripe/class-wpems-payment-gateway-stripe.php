<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class WPEMS_Payment_Gateway_Stripe extends WPEMS_Abstract_Payment_Gateway {

	/**
	 * id of payment
	 * @var null
	 */
	public $id = 'stripe';

	/**
	 * @var string
	 */
	private $api_endpoint = 'https://api.stripe.com/v1/';

	/**
	 * @var object
	 */
	private $charge = null;

	/**
	 * @var array
	 */
	private $form_data = array();

	/**
	 * @var string|null
	 */
	public $title = null;

	/**
	 * @var array|null
	 */
	protected $settings = null;

	/**
	 * @var null
	 */
	protected $posted = null;

	/**
	 * Request Token
	 *
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var null
	 */
	protected $publish_key = null;

	/**
	 * @var null
	 */
	protected $secret_key = null;

	/**
	 * @var null
	 */
	protected $coupon_codes = null;

	/**
	 * @var null
	 */
	protected $booking = null;

	// enable
	protected static $enable = false;

	public function __construct() {
		
		$this->title = __( 'Stripe', 'etbi' );
		// $this->icon = WPEMS_INC_URI . '/gateways/' . $this->id . '/' . $this->id . '.png';

		// Add default values for fresh installs
		if ( wpems_get_option( 'stripe_enable' ) ) {

			$this->coupon_codes = wpems_get_coupon_codes();

			$this->settings                     = array();
			$this->settings['test_mode']        = wpems_get_option( 'stripe_enable_test' );
			$this->settings['test_publish_key'] = wpems_get_option( 'stripe_test_publish_key' );
			$this->settings['test_secret_key']  = wpems_get_option( 'stripe_test_secret_key' );
			$this->settings['live_publish_key'] = wpems_get_option( 'stripe_live_publish_key' );
			$this->settings['live_secret_key']  = wpems_get_option( 'stripe_live_secret_key' );

			// API Info
			$this->publish_key = $this->settings['test_mode'] == 'yes' ? $this->settings['test_publish_key'] : $this->settings['live_publish_key'];
			$this->secret_key  = $this->settings['test_mode'] == 'yes' ? $this->settings['test_secret_key'] : $this->settings['live_secret_key'];

		}

		parent::__construct();

		// // init process
		//add_action( 'init', array( $this, 'payment_validation' ), 99 );
	}

	/*
	 * Check gateway available
	 */
	public function is_available() {
		return true;
	}

	/*
	 * Check gateway enable
	 */
	public function is_enable() {
		self::$enable = wpems_get_option( 'stripe_enable' ) === 'yes' && $this->has_keys();
		return apply_filters( 'tp_event_enable_stripe_payment', self::$enable );
	}


	public function has_keys() {

		if( !empty( $this->publish_key ) && !empty( $this->secret_key ) ) {

			return true;

		}

		return false;

	}

	/**
	 * Post data and get json.
	 *
	 * @param $post_data
	 * @param string $post_location
	 *
	 * @return object
	 * @throws string
	 */
	public function post_data( $post_data, $post_location = 'charges' ) {

		$response = wp_remote_post( $this->api_endpoint . $post_location, array(
			'method'     => 'POST',
			'headers'    => array(
				'Authorization' => 'Basic ' . base64_encode( $this->secret_key . ':' ),
			),
			'body'       => $post_data,
			'timeout'    => 70,
			'sslverify'  => false,
			'user-agent' => 'ETBI Stripe',
		) );

		return $this->parse_response( $response );
	}

	/**
	 * Parse response.
	 *
	 * @param $response
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public function parse_response( $response ) {
		if ( is_wp_error( $response ) ) {

			throw new Exception( 'error' );
		}

		if ( empty( $response['body'] ) ) {
			throw new Exception( 'error' );
		}

		$parsed_response = json_decode( $response['body'] );

		return $parsed_response;
	}

	/**
	 * fields settings
	 * @return array
	 */
	public function admin_fields() {
		$prefix        = 'thimpress_events_';
		$stripe_enable = wpems_get_option( 'stripe_enable' );
		return apply_filters( 'learn-press/gateway-payment/stripe/settings',
				array(
					array(
						'type'  => 'section_start',
						'id'    => 'stripe_settings',
						'title' => __( 'Stripe Settings', 'wp-events-manager' ),
						'desc'  => esc_html__( 'Make payment via Stripe', 'wp-events-manager' )
					),
					array(
						'type'    => 'yes_no',
						'title'   => __( 'Enable', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_enable',
						'default' => 'no',
						'desc'    => apply_filters( 'tp_event_filter_enable_stripe_gateway', '' )
					),
					array(
						'type'    => 'text',
						'title'   => __( 'Live secret key', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_live_secret_key',
						'default' => '',
						'class'   => 'stripe-live-secret-key' . ( $stripe_enable == 'no' ? ' hide-if-js' : '' )
					),
					array(
						'type'    => 'text',
						'title'   => __( 'Live publish key', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_live_publish_key',
						'default' => '',
						'class'   => 'stripe-live-publish-key' . ( $stripe_enable == 'no' ? ' hide-if-js' : '' )
					),
					array(
						'type'    => 'yes_no',
						'title'   => __( 'Enable Test Mode', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_enable_test',
						'default' => 'no',
						'desc'    => apply_filters( 'tp_event_filter_enable_test_stripe_gateway', '' )
					),
					array(
						'type'    => 'text',
						'title'   => __( 'Test secret key', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_test_secret_key',
						'default' => '',
						'class'   => 'stripe-test-secret-key' . ( $stripe_enable == 'no' ? ' hide-if-js' : '' )
					),
					array(
						'type'    => 'text',
						'title'   => __( 'Test publish key', 'wp-events-manager' ),
						'id'      => $prefix . 'stripe_test_publish_key',
						'default' => '',
						'class'   => 'stripe-test-publish-key' . ( $stripe_enable == 'no' ? ' hide-if-js' : '' )
					),
					array(
						'type' => 'section_end',
						'id'   => 'stripe_settings'
					)
				)
			);
	}

	/**
	 * get_item_name
	 * @return string
	 */
	public function get_item_name( $booking_id = null ) {
		if ( !$booking_id )
			return;

		// book
		$book        = WPEMS_Booking::instance( $booking_id );
		$description = sprintf( '%s(%s)', $book->post->post_title, wpems_format_price( $book->price, $book->currency ) );

		return $description;
	}

	/**
	 * send to stripe url
	 * @return url string
	 */
	public function send_to_stripe() {
		if ( ! $this->booking ) {
			wp_send_json( array(
				'status'  => false,
				'message' => __( 'Booking does not exist!', 'wp-events-manager' )
			) );
			die();
		}

		if ( $this->get_form_data() ) {

			$amount = $this->form_data['amount'];

			if( $amount == 0 ) {

				$status = 'ea-completed';
				$this->booking->update_status( $status );

				return array( 'free_coupon' => true );


			}

			$stripe_charge_data['amount']      = $amount; // amount in cents
			$stripe_charge_data['currency']    = $this->form_data['currency'];
			$stripe_charge_data['capture']     = 'true';
			$stripe_charge_data['expand[]']    = 'balance_transaction';
			$stripe_charge_data['card']        = $this->form_data['token'];
			$stripe_charge_data['description'] = $this->form_data['description'];

			$charge       = $this->post_data( $stripe_charge_data );
			$this->charge = $charge;

			return $charge;
		}

		return false;
	}

	/**
	 * Get form data.
	 *
	 * @return array
	 */
	public function get_form_data() {
		if ( $this->booking ) {

			$user  = get_userdata( $this->booking->user_id );
			$email = $user->user_email;

			$this->form_data = array(
				'amount'      => (float) $this->booking->price * 100,
				'currency'    => strtolower(  wpems_get_currency() ),
				'token'       => $this->token,
				'description' => sprintf( "Booking charge for %s", $email ),
				'customer'    => array(
					'name'          => $user->display_name,
					'billing_email' => $email,
				),
				'errors'      => isset( $this->posted['form_errors'] ) ? $this->posted['form_errors'] : '',
				'coupon_code' => (string) $this->booking->coupon_code
			);
		}

		return $this->form_data;
	}

	public function validate_fields( $form_data ) {

		$posted = $form_data['wpems-stripe'];
		// $coupon 	   = ! empty( $form_data['coupon_code'] ) ? $form_data['coupon_code'] : null;
		$card_number   = ! empty( $posted['card_number'] ) ? $posted['card_number'] : null;
		$expiry_month  = ! empty( $posted['expiry_month'] ) ? $posted['expiry_month'] : 1;
		$expiry_year   = ! empty( $posted['expiry_year'] ) ? $posted['expiry_year'] : ( (int) date( 'Y', time() ) ) + 1;
		$card_expiry   = $expiry_month . '/' . $expiry_year;
		$card_code     = ! empty( $posted['card_code'] ) ? $posted['card_code'] : null;
		$error_message = array();
		if( $this->booking->price > 0 ) {
			if ( empty( $card_number ) ) {
				$error_message[] = __( 'Card number is empty.', 'wp-events-manager' );
			}
			if ( empty( $card_expiry ) ) {
				$error_message[] = __( 'Card expiry is empty.', 'wp-events-manager' );
			}
			if ( empty( $card_code ) ) {
				$error_message[] = __( 'Card code is empty.', 'wp-events-manager' );
			}			

			if ( empty( $error_message ) ) {

				$token = $this->post_data( array(
					'card' => array(
						'number'    => $card_number,
						'exp_month' => $expiry_month,
						'exp_year'  => $expiry_year,
						'cvc'       => $card_code,
					)
				), 'tokens' );

				if ( isset( $token->id ) ) {
					$this->token = $token->id;
				} else if ( ! empty ( $token->error ) ) {
					$error_message[] = sprintf( '%s', $token->error->message );
				}
			}
		}
		if ( $error = sizeof( $error_message ) ) {
			throw new Exception( sprintf( '<div>%s</div>', join( '</div><div>', $error_message ) ), 8000 );
		}
		$this->posted = $posted;

		return $error ? false : true;

	}

	public function get_event_url( $booking ) {

		return get_permalink( $booking->event_id );
	}

	public function process( $booking_id = false ) {

		if ( !$this->is_available() ) {
			return array(
				'status'  => false,
				'message' => __( 'Please contact administrator to setup Stripe.', 'wp-events-manager' )
			);
		}

		$this->booking = wpems_get_booking( $booking_id );

		if( $validated = $this->validate_fields( $_POST ) ) {

			$stripe = $this->send_to_stripe();

			if( is_array( $stripe ) && $stripe['free_coupon'] ) {

				$event_id = $this->booking->event_id;
				$event_title = get_the_title( $event_id );
				$booking_qty =  $this->booking->qty;
				$success_message = sprintf( esc_html( _n( 'You have successfully registered for %1$d ticket for %2$s', 'You have successfully registered for %1$d tickets for %2$s', $booking_qty, 'wp-events-manager' ) ), $booking_qty, $event_title );

				$result = array(
					'status'   => true,
					'message' => $success_message,
					'event' =>  'purchased',
					'account_url' => wpems_account_url(),
					'url' => $this->get_event_url( $this->booking )
				);

			} else if ( ! empty( $stripe->error->message ) ) {

				$result = array(
					'status' => false,
					'message' => $stripe->error->message,
					'url' => $this->get_event_url( $this->booking )
				);

			} else {

				$payment_status = $stripe->status;

				if( in_array( $payment_status, array( 'succeeded', 'pending' ) ) ) {
					$status = 'ea-completed';
					$this->booking->update_status( $status );
				}

				$event_id = $this->booking->event_id;
				$event_title = get_the_title( $event_id );
				$booking_qty =  $this->booking->qty;
				$success_message = sprintf( esc_html( _n( 'You have successfully purchased %1$d ticket for %2$s.', 'You have successfully purchased %1$d tickets for %2$s.', $booking_qty, 'wp-events-manager' ) ), $booking_qty, $event_title );

				$result = array(
					'status'   => true,
					'message' => $success_message,
					'url' => $this->get_event_url( $this->booking ),
					'event' =>  'purchased',
					'data'	=> $payment_status
				);

				if( is_user_logged_in() ) {
					$result['account_url'] = wpems_account_url();
				} else {
					$result['message'] .= ' Check your email.';
				}


			}

		} else {

			$result = array(
				'status'	=> false,
				'message'	=> 'There was an error processing your credit card, please try again.'
			);

		}
		
		return $result;
	}

}
