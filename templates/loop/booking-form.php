<?php
/**
 * @Author: ducnvtt
 * @Date  :   2016-03-03 10:34:45
 * @Last  Modified by:   leehld
 * @Last  Modified time: 2017-02-03 15:50:46
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$event    = new WPEMS_Event( get_the_ID() );
$user_reg = $event->booked_quantity( get_current_user_id() );
$payments = wpems_gateways_enable();
if( version_compare(WPEMS_VER, '2.1.5', '>=') ) {
    $is_expired = ( 'expired' === get_post_meta( get_the_ID(), 'tp_event_status', true ) ) ? true : false;
} else {
    $is_expired = ( 'tp-event-expired' === $event->post->post_status ) ? true : false;
}

?>
<h3 class="book-title"><?php esc_html_e( 'Buy Ticket', 'eduma' ); ?></h3>

<div class="event_register_area">

    <form name="event_register" class="event_register" method="POST">

        <ul class="info-event">
            <li>
                <div class="label"><?php esc_html_e( 'Total Slots', 'eduma' ); ?></div>
                <div class="value"><?php echo absint( $event->qty ); ?></div>
            </li>
            <li>
                <div class="label"><?php esc_html_e( 'Booked Slots', 'eduma' ); ?></div>
                <div class="value"><?php echo esc_html( $event->booked_quantity() ); ?></div>
            </li>
            <li class="event-cost">
                <div class="label"><?php esc_html_e( 'Cost', 'eduma' ); ?></div>
                <div class="value"><?php echo ( $event->get_price() ) ? wpems_format_price( $event->get_price() ) . esc_html__( '/Slot', 'eduma' ) : '<span class="free">' . esc_html__( 'Free', 'eduma' ) . '</span>'; ?></div>
            </li>
            <li>
<?php if( wpems_get_option( 'allow_guest_checkout' ) === 'yes' || ( ! $event->is_free() && is_user_logged_in() ) || $event->is_free() ) { ?>
            <?php if( ! is_user_logged_in() ) { ?>

                <div class="label"><?php esc_html_e( 'Quantity', 'eduma' ); ?></div>
                    <div class="value">
                        <input disabled type="number" value="1" min="1" id="event_register_qty"/>
                        <input type="hidden" name="qty" value="1" min="1"/>
                    </div>

            <?php } else { ?>
                <?php if ( $user_reg == 0 && $event->is_free() && wpems_get_option( 'email_register_times' ) === 'once' ) : ?>
                    <div class="label"><?php esc_html_e( 'Quantity', 'eduma' ); ?></div>
                    <div class="value">
                        <input disabled type="number" value="1" min="1" id="event_register_qty"/>
                        <input type="hidden" name="qty" value="1" min="1"/>
                    </div>
                <?php else : ?>
                    <div class="label"><?php esc_html_e( 'Quantity', 'eduma' ); ?></div>
                    <div class="value">
                        <input type="number" name="qty" value="1" min="1" max="<?php echo $event->qty;?>" id="event_register_qty"/>
                    </div>
                <?php endif; ?>

            <?php } ?>
            </li>
             <?php if( ! is_user_logged_in() ) { ?>
                <li class="wpems-name-item">

                    <p class="wpems-form-row events_name_area" >
                        <label><?php echo wp_kses( __( 'Your Name (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="name" id="wpems-name"
                               value="" placeholder="Your name" class="event-form-input" required="true"/>
                    </p>
                    
                </li>
                <li class="wpems-name-item">

                    <p class="wpems-form-row events_email_area" >
                        <label><?php echo wp_kses( __( 'Your Email (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="email" id="wpems-email"
                               value="" placeholder="Your email" class="event-form-input" required="true"/>
                    </p>
                    
                </li>
            <?php } else { 

                $user = wp_get_current_user();
                $name = $user->display_name;

                if( empty( $name ) ) {

                    $name = $user->user_firstname . ' ' . $user->user_lastname;

                }

                if( empty( $name ) ) {

                    $name = $user->user_login;

                }

                ?>
                <li class="wpems-name-item">

                    <p class="wpems-form-row events_name_area" >
                        <label><?php echo wp_kses( __( 'Your Name (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="name" id="wpems-name"
                               value="<?php esc_attr_e( $name ); ?>" placeholder="Your name" class="event-form-input"/>
                    </p>
                    
                </li>
                <li class="wpems-name-item">

                    <p class="wpems-form-row events_email_area" >
                        <label><?php echo wp_kses( __( 'Your Email (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="email" id="wpems-email"
                               value="<?php esc_attr_e( $user->user_email ); ?>" placeholder="Your email" class="event-form-input"/>
                    </p>
                    
                </li>
            <?php } ?>
                <li class="wpems-location-item">

                    <p class="wpems-form-row events_name_area" >
                        <label><?php echo wp_kses( __( 'Your Location (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="location" id="wpems-location"
                               value="" placeholder="Your location" class="event-form-input" required="true"/>
                    </p>
                    
                </li>
                <li class="wpems-organization-item">


                    <p class="wpems-form-row events_organization_area" >
                        <label><?php echo wp_kses( __( 'Your Organization (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="organization" id="wpems-organization"
                               value="" placeholder="Your organization" class="event-form-input" required="true"/>
                    </p>
                    
                </li>
                <li class="wpems-degree-item">

                    <p class="wpems-form-row events_degree_area" >
                        <label><?php echo wp_kses( __( 'Your Degree (Required)', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="degree" id="wpems-degree"
                               value="" placeholder="Your degree" class="event-form-input" required="true"/>
                    </p>
                    
                </li>
            
            <?php if ( intval( $event->get_price() ) > 0 ) : ?>
                <?php
                if ( ! empty( $payments ) ) {
                ?>
                <li class="event-payment">

                    <p class="wpems-form-row" >
                        <label><?php echo wp_kses( __( 'Coupon', 'wpems' ), array( 'span' => array() ) ); ?></label>
                        <input type="text" name="coupon_code" id="wpems-coupon-code"
                               value="" placeholder="coupon"/>
                        <?php wp_nonce_field( 'event_apply_coupon_nonce', 'event_apply_coupon_nonce' ); ?>
                        <span class="input-group-btn apply-coupon-input-btn">
                            <button type="button" id="wpems-coupon-code-btn" class="coupon-code-button button"><?php _e( 'Apply Coupon', 'wpems' ); ?></button>
                        </span>
                    </p>



                    <div class="event_auth_payment_methods">
                        <ul class="payment-list">


                        <?php
                            $i = 0;
                            $only_one = ( count( $payments ) == 1 ) ? 'hidden' : '';
                            foreach ( $payments as $id => $payment ) :
                                ?>
                                <li>
                                <input id="payment_method_<?php echo esc_attr( $id ); ?>" type="radio" name="payment_method" value="<?php echo esc_attr( $id ); ?>"<?php echo 0 === $i ? ' checked' : ''; ?> class="<?php esc_attr_e($only_one); ?>"/>

                                <?php if( $id !== 'stripe' ) { ?>
                                    <label for="payment_method_<?php echo esc_attr( $id ); ?>"><img width="115" height="50" src="<?php echo esc_attr( $payment->icon ); ?>"/></label>
                                <?php } ?>

                                <?php if( $id === 'stripe' ) : ?>









                                    <div id="wpems-stripe-form">
                                        <p class="wpems-form-row">
                                            <label><?php echo wp_kses( __( 'Card Number <span class="required">*</span>', 'wpems-stripe' ), array( 'span' => array() ) ); ?></label>
                                            <input type="text" name="wpems-stripe[card_number]" id="wpems-stripe-payment-card-number"
                                                   maxlength="19" value="" autocomplete="cc-number" placeholder="•••• •••• •••• ••••"  class="event-form-input"/>
                                        </p>
                                        <p class="wpems-form-row">
                                            <label><?php echo wp_kses( __( 'Expiry (MM/YY) <span class="required">*</span>', 'wpems-stripe' ), array( 'span' => array() ) ); ?></label>
                                            <select class="wpems-stripe-expiry" name="wpems-stripe[expiry_month]">
                                                <option value=01>01</option>
                                                <option value=02>02</option>
                                                <option value=03>03</option>
                                                <option value=04>04</option>
                                                <option value=05>05</option>
                                                <option value=06>06</option>
                                                <option value=07>07</option>
                                                <option value=08>08</option>
                                                <option value=09>09</option>
                                                <option value=10>10</option>
                                                <option value=11>11</option>
                                                <option value=12>12</option>
                                            </select>
                                            <select class="wpems-stripe-expiry" name="wpems-stripe[expiry_year]">
                                                <?php for ( $a = (int) date( 'Y', time() ), $b = $a + 10, $i = $a; $i < $b; $i ++ ) { ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php } ?>
                                            </select>
                                        </p>
                                        <p class="wpems-form-row">
                                            <label><?php echo wp_kses( __( 'Card Code <span class="required">*</span>', 'wpems-stripe' ), array( 'span' => array() ) ); ?></label>
                                            <input type="text" name="wpems-stripe[card_code]" id="wpems-stripe-payment-card-code" value=""
                                                   placeholder="•••"  class="event-form-input"/>
                                        </p>
                                    </div>
                                    <?php if ( wpems_get_option( 'stripe_enable_test' ) === 'yes' ) { ?>
                                        <!--<?php esc_html_e( 'Test mode is enabled. You can use the card number 4242424242424242 with any CVC and a valid expiration date for testing purpose.', 'wpems-stripe' ); ?>-->
                                    <?php } ?>











                                <?php endif; ?>
                                </li>
                                <?php
                                $i ++;
                            endforeach;
                        ?>
                        </ul>
                    </div>







                </li>
                    <?php }?>
            <?php endif; ?>
        </ul>

        <?php if(empty($payments)) { ?>
            <p class="event_auth_register_message_error">
                <?php echo esc_html__( 'You must set payment setting!', 'eduma' ); ?>
            </p>
        <?php }?>


<?php } //( ! $event->is_free() && is_user_logged_in() ) || $event->is_free() ?>


        <!--Hide payment option when cost is 0-->

        <!--End hide payment option when cost is 0-->

        <div class="event_register_foot">
            <input type="hidden" name="event_id" value="<?php echo esc_attr( get_the_ID() ); ?>"/>
            <input type="hidden" name="action" value="event_auth_register"/>
            <?php wp_nonce_field( 'event_auth_register_nonce', 'event_auth_register_nonce' ); ?>
            <?php if ( $is_expired ) : ?>
                <button type="submit" disabled class="event_button_disable"><?php esc_html_e( 'Expired', 'eduma' ); ?></button>
            <?php elseif ( absint( $event->qty ) == 0 ) : ?>
                <button type="submit" disabled class="event_button_disable"><?php esc_html_e( 'Sold Out', 'eduma' ); ?></button>
            <?php else : ?>
                <?php if( wpems_get_option( 'allow_guest_checkout' ) === 'no' ) { ?>
                    <?php if ( ! is_user_logged_in() && ! $event->is_free() ) { ?>
                        <a href="<?php echo esc_url( add_query_arg( 'redirect_to', urlencode( get_permalink() ), thim_get_login_page_url() ) ); ?>" class="event_register_submit event_auth_button"><?php esc_html_e( 'Login Now', 'eduma' ); ?></a>
                        <p></p>
                        <p class="login-notice">
                            <?php echo esc_html__( 'You must login to our site to book this event!', 'eduma' ); ?>
                        </p>
                    <?php } else { ?>
                        <button type="submit" class="event_register_submit event_auth_button" <?php echo $payments ? '' : 'disabled="disabled"' ?>><?php esc_html_e( 'Book Now', 'eduma' ); ?></button>
                    <?php } ?>
                 <?php } else { ?>
                    <button type="submit" class="event_register_submit event_auth_button" <?php echo $payments ? '' : 'disabled="disabled"' ?>><?php esc_html_e( 'Book Now', 'eduma' ); ?></button>
                <?php } ?>
            <?php endif ?>

        </div>

    </form>

</div>
