<?php 
global $post;
$post_id    = $post->ID;
$prefix     = 'tp_event_';
$date_start = get_post_meta( $post->ID, $prefix . 'date_start', true ) ? date( 'M d, Y', strtotime( get_post_meta( $post->ID, $prefix . 'date_start', true ) ) ) : '';
$time_start = get_post_meta( $post->ID, $prefix . 'time_start', true ) ? date( 'H:i', strtotime( get_post_meta( $post->ID, $prefix . 'time_start', true ) ) ) : '';

$date_end = get_post_meta( $post->ID, $prefix . 'date_end', true ) ? date( 'M d, Y', strtotime( get_post_meta( $post->ID, $prefix . 'date_end', true ) ) ) : '';
$time_end = get_post_meta( $post->ID, $prefix . 'time_end', true ) ? date( 'H:i', strtotime( get_post_meta( $post->ID, $prefix . 'time_end', true ) ) ) : '';

$program = get_post_meta( $post->ID, $prefix . 'program', true );
$is_program = ( $program == 'true' ) ? true : false;

if( $has_thumbnail = get_the_post_thumbnail_url(null, array(96, 72)) ) {

	$thumbnail = '<img src="'.$has_thumbnail.'" width="96" height="72" class="event-thumbnail" >';

} else {
	$default_thumbnail = WPEMS_ASSETS_URI . 'images/event-thumb-placeholder.png';
	$thumbnail = '<img src="'.$default_thumbnail.'" width="96" height="72" class="event-thumbnail" >';

}

$event_preview = '<a href="'.get_the_post_thumbnail_url(null).'" title="View larger image" class="ui-icon ui-icon-zoomin">View larger</a>';
$event_preview = '';

?>

<li id="event-<?php the_ID(); ?>" data-id="<?php the_ID(); ?>" <?php post_class('ui-widget-content ui-corner-tr event-item ui-draggable ui-draggable-handle'); ?>>
	<input type="hidden" name="event-id" class="event-id" value="<?php the_ID(); ?>">
	<?php echo $thumbnail; ?>
	<h5 class="event-title"><?php echo esc_html( get_the_title() ); ?></h5>
	<small class="event-date-time"><span class="icon dashicons dashicons-calendar"></span><?php printf( __( '%1$s to %2$s' ), $date_start, $date_end ) ?></small>
	<?php echo ( $is_program ) ? '<strong class="is-program-event"><small>'.__( 'Program', 'etbi' ).'</small></strong>' : ''; ?>
	<?php echo $event_preview; ?>
	<a href="<?php echo get_edit_post_link( $post_id ); ?>" title="Edit this event" class="event-edit-link" target="_blank"><small><span class="dashicons dashicons-edit"></span>Edit</small></a>
	<?php if( $is_program ) : ?>

		<?php $program_events = (array) maybe_unserialize( get_post_meta( $post->ID, $prefix . 'event_ids', true ) ); ?>

		<ul class="program-events">

		<?php foreach( $program_events as $program_event_id ) : ?>

			<?php 

				if( $has_program_event_thumbnail = get_the_post_thumbnail_url( $program_event_id, array(96, 72) ) ) {

					$program_event_thumbnail = '<img src="'.$has_program_event_thumbnail.'" width="30" height="30" class="program-event-thumbnail" >';

				} else {
					$default_thumbnail = WPEMS_ASSETS_URI . 'images/event-thumb-placeholder.png';
					$program_event_thumbnail = '<img src="'.$default_thumbnail.'" width="30" height="30" class="program-event-thumbnail" >';

				}

			?>

			<li id="event-<?php $program_event_id ?>" class="event-program">
				<?php echo $program_event_thumbnail; ?>
				<h6 class="event-title"><?php echo esc_html( get_the_title( $program_event_id ) ); ?></h6>
			</li>			

		<?php endforeach; ?>		

		</ul>

	<?php endif; ?>
</li>