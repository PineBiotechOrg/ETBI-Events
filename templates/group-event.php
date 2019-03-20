<?php


global $post;

$display_year = get_theme_mod( 'thim_event_display_year', false );
$class        = 'item-event';
$time_format  = get_option( 'time_format' );
$time_start   = wpems_event_start( $time_format );
$time_end     = wpems_event_end( $time_format );

$location   = wpems_event_location();
$location_link = wpems_event_location_link();
$date_show  = wpems_get_time( 'd' );
$month_show = wpems_get_time( 'F' );

$prefix = 'tp_event_';
$selected = '';
$event_id = $post->ID;

$date_start = get_post_meta( $event_id, $prefix . 'date_start', true );
$time_start = get_post_meta( $event_id, $prefix . 'time_start', true );

$date_end = get_post_meta( $event_id, $prefix . 'date_end', true );
$time_end = get_post_meta( $event_id, $prefix . 'time_end', true );



$data_date = date( 'd/m/Y', strtotime( $date_start ) );
$todays_date = time();
$current_event_date_start = strtotime( $date_start );

if( $current_event_date_start > $todays_date && $prev_event_date_start < $todays_date ) {

	$selected = 'class="selected"';

}

$prev_event_date_start = $current_event_date_start;

						


if ( $display_year ) {
	$month_show .= ', ' . wpems_get_time( 'Y' );
}
?>
<li <?php post_class( $class ); ?>>
    <div class="time-from">
        <div class="date">
			<?php echo esc_html( $date_show ); ?>
        </div>
        <div class="month">
			<?php echo esc_html( $month_show ); ?>
        </div>
    </div>
	<?php
	if ( has_post_thumbnail() ) {
		echo '<div class="image">';
		echo '<a href="'. esc_url( get_permalink( get_the_ID() ) ) .'" class="event-img">'. thim_get_feature_image( get_post_thumbnail_id(), 'full', 450, 233 ) .'</a>';
        echo '<a href="'. esc_url( get_permalink( get_the_ID() ) ) .'" class="register-now-btn button btn btn-primary">'. __( 'Register Now', 'etbi' ) .'</a>';
		echo '</div>';
	}
	?>
    <div class="event-wrapper">
        <h5 class="title">
            <a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>"> <?php echo get_the_title(); ?></a>
        </h5>

        <div class="meta">
            <div class="time">
                <i class="fa fa-clock-o"></i>
				<?php echo esc_html( $time_start ) . ' - ' . esc_html( $time_end ); ?>
            </div>
            <div class="location">
                <i class="fa fa-map-marker"></i>
                <?php 

                    if( ! empty( $location_link ) ) {

                        echo '<a href="'. esc_url( $location_link ) .'">'.ent2ncr( $location ).'</a>';

                    } else {

                        echo ent2ncr( $location );

                    }

                ?>
            </div>
        </div>
        <div class="description">
			<?php echo thim_excerpt( 25 ); ?>
        </div>
    </div>

</li>
