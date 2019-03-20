<?php
/**
 * The Template for displaying email register event for user.
 *
 * Override this template by copying it to yourtheme/wp-events-manager/emails/register-event.php
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/Template
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

if ( ! $booking || ! $user ) {
    return;
} 

$time_format = get_option( 'time_format' );
$time_from   = get_post_meta( $booking->event_id, 'tp_event_date_start', true ) ? strtotime( get_post_meta( $booking->event_id, 'tp_event_date_start', true ) ) : time();
$time_finish = get_post_meta( $booking->event_id, 'tp_event_date_end', true ) ? strtotime( get_post_meta( $booking->event_id, 'tp_event_date_end', true ) ) : time();
$time_start  = wpems_event_start( $time_format );
$time_end    = wpems_event_end( $time_format );

$location = get_post_meta( $booking->event_id, 'tp_event_location', true ) ? get_post_meta( $booking->event_id, 'tp_event_location', true ) : '';
$location_link = get_post_meta( $booking->event_id, 'tp_event_location_link', true ) ? get_post_meta( $booking->event_id, 'tp_event_location_link', true ) : '';

if( $location ) {

    if( ! empty( $location_link ) ) {

        $location = '<p class="event-location"><a href="'.esc_url( $location_link ).'">'. esc_html( $location ) .'</a></p>';

    } else {

        $location = '<p class="event-location">'. esc_html( $location ) .'</p>';

    }

}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <style type="text/css">
        table td,
        table th {
            font-size: 13px;
            padding: 5px 30px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="event_auth_admin_table_booking">      
    <div class="event-thumbnail">
        <a href="<?php echo get_permalink( $booking->event_id ) ?>"><img src="<?php echo esc_attr( get_the_post_thumbnail_url( $booking->event_id, [1200, 600] ) ); ?>" width="600" height="" alt="alt_text" border="0" style="width: 100%; max-width: 600px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 15px; color: #555555; margin: auto;" class="g-img"></a>
    </div>

<h3><?php printf( __( 'Hello %s!', 'wp-events-manager' ), $user['user_name'] ); ?></h3>
<?php
printf(
    __( 'You have successfully registered to <a href="%s">our event</a>. Please go to the following link for more details.<a href="%s">Your account.</a>', 'wp-events-manager' ), get_permalink( $booking->event_id ), wpems_account_url()
);
?>

    <h2 class="event-title" style="color: #333"><a href="<?php echo get_permalink( $booking->event_id ) ?>" style="color: #333;"><?php echo get_the_title( $booking->event_id ) ?></a></h2>

    <div class="event-info">
        <p class="event-date"><?php echo $date_start; ?></p>
        <?php echo $location; ?>
    </div>

    <div class="event-excerpt">
        <p>
            <?php echo get_the_excerpt( $booking->event_id ); ?>
        </p>
    </div>



<table class="event_auth_admin_table_booking" style="width:100%; margin: 0 auto 2em !important">
    <thead>
    <tr>
        <th><?php _e( 'ID', 'wp-events-manager' ) ?></th>
        <th><?php _e( 'Quantity', 'wp-events-manager' ) ?></th>
        <th><?php _e( 'Paid', 'wp-events-manager' ) ?></th>
        <th><?php _e( 'Status', 'wp-events-manager' ) ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php printf( '%s', wpems_format_ID( $booking->ID ) ) ?></td>
        <td><?php printf( '%s', $booking->qty ) ?></td>
        <td><?php printf( '%s', wpems_format_price( floatval( $booking->price ), $booking->currency ) ) ?></td>
        <td>
            <?php
            $return   = array();
            $return[] = sprintf( '%s', wpems_booking_status( $booking->ID ) );
            $return[] = $booking->payment_id ? sprintf( '(%s)', wpems_get_payment_title( $booking->payment_id ) ) : '';
            $return   = implode( '', $return );
            printf( '%s', $return );

            ?>
        </td>
    </tr>
    </tbody>
</table>

<div class="event-ticket-link">
    <?php
        printf(
            __( '<a class="button-a button-a-primary" href="%s" style="background: #ffb606; border: 1px solid #ffb606; font-family: sans-serif; font-size: 15px; line-height: 15px; text-decoration: none; padding: 13px 17px; color: #333; display: block; text-align: center;">Your Account</a>', 'wp-events-manager' ), wpems_account_url()
        );
    ?>
</div>
  
</div>


</body>
</html>