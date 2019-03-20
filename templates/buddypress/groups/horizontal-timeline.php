<?php if( $query->have_posts() ) : ?>
	<section class="cd-horizontal-timeline">

		<div class="event-timeline-header">
			<h2 class="upcoming-event-heading">Upcoming Events</h2>

			<?php if( current_user_can( 'edit_posts' ) ) : //is_user_logged_in() && groups_is_user_member( get_current_user_id(), bp_get_current_group_id() ) ?>

				<form name="register_all_events" class="register-all-events-form" method="POST">
					<input type="hidden" name="group_id" value="<?php echo esc_attr( bp_get_current_group_id() ); ?>" >
					<input type="hidden" name="action" value="register_all_events"/>
					 <?php wp_nonce_field( 'register_all_events_nonce', 'register_all_events_nonce' ); ?>
					<button type="submit" class="button register-all-events-btn" >Register to all events</button>
				</form>

			<?php endif; ?>
		</div>
		<div class="horizontal-timeline">
			<div class="events-wrapper">
				<div class="group-events">
					<ol>

						<?php if( $query->have_posts() ) : ?>

							<?php

								$prev_event_date_start = null;
								$prev_event_date_end = null;

							?>

							<?php while ( $query->have_posts() ) : $query->the_post() ?>

								<?php

									global $post;

									$prefix = 'tp_event_';
									$selected = '';
									$event_id = $post->ID;

									$date_start = get_post_meta( $event_id, $prefix . 'date_start', true );
									$time_start = get_post_meta( $event_id, $prefix . 'time_start', true );

									$date_end = get_post_meta( $event_id, $prefix . 'date_end', true );
									$time_end = get_post_meta( $event_id, $prefix . 'time_end', true );

									$program = get_post_meta( $event_id, $prefix . 'program', true );
									$is_program = ( $program == 'true' ) ? true : false; 

									if( $is_program ) {
										continue;
									}

									

									$data_date = date( 'd/m/Y', strtotime( $date_start ) );
									$todays_date = time();
									$current_event_date_start = strtotime( $date_start );

									if( $current_event_date_start >= $todays_date && $prev_event_date_start <= $todays_date ) {

										$selected = 'class="selected"';

									} else if( $current_event_date_start < $todays_date && ( ( $query->current_post + 1 ) == $query->post_count ) ) {

										$selected = 'class="selected"';

									}

									$prev_event_date_start = $current_event_date_start;

									

								?>
							
								<li><a href="#0" data-date="<?php echo esc_attr( $data_date ); ?>" <?php echo $selected; ?> ><?php echo esc_html( date( 'j M', strtotime($date_start) ) ); ?></a></li>

							<?php endwhile; ?>

							 <?php wp_reset_postdata(); ?>

						<?php endif; ?>
					</ol>

					<span class="filling-line" aria-hidden="true"></span>
				</div> <!-- .events -->
			</div> <!-- .events-wrapper -->
				
			<ul class="cd-timeline-navigation">
				<li><a href="#0" class="prev inactive">Prev</a></li>
				<li><a href="#0" class="next">Next</a></li>
			</ul> <!-- .cd-timeline-navigation -->
		</div> <!-- .timeline -->

		<div class="events-content">
			<ol class="tab-content thim-list-event">

				<?php if( $query->have_posts() ) : ?>

					<?php

						$prev_event_date_start = null;
						$prev_event_date_end = null;

					?>

					<?php while( $query->have_posts() ) : $query->the_post() ?>
					
						<?php

							global $post;

							$display_year = get_theme_mod( 'thim_event_display_year', false );
							$class        = 'item-event';
							$time_format  = get_option( 'time_format' );
							$time_start   = wpems_event_start( $time_format );
							$time_end     = wpems_event_end( $time_format );

							$location   = wpems_event_location();
							$location_link = wpems_event_location_link();
							$date_show  = wpems_get_start_time( 'd' );
							$month_show = wpems_get_start_time( 'F' );

							$prefix = 'tp_event_';
							$selected = '';
							$event_id = $post->ID;

							$date_start = get_post_meta( $event_id, $prefix . 'date_start', true );
							$time_start = get_post_meta( $event_id, $prefix . 'time_start', true );

							$date_end = get_post_meta( $event_id, $prefix . 'date_end', true );
							$time_end = get_post_meta( $event_id, $prefix . 'time_end', true );

							$program = get_post_meta( $event_id, $prefix . 'program', true );
							$is_program = ( $program == 'true' ) ? true : false; 

							if( $is_program ) {
								continue;
							}


							$data_date = date( 'd/m/Y', strtotime( $date_start ) );
							$todays_date = time();
							$current_event_date_start = strtotime( $date_start );

							if( $current_event_date_start >= $todays_date && $prev_event_date_start <= $todays_date ) {

								$class .= ' selected';

							} else if( $current_event_date_start < $todays_date && ( ( $query->current_post + 1 ) == $query->post_count ) ) {

								$class .= ' selected';

							}

							$prev_event_date_start = $current_event_date_start;

													


							if ( $display_year ) {
								$month_show .= ', ' . wpems_get_time( 'Y' );
							}
						?>

							<li <?php post_class( $class ); ?> data-date="<?php echo esc_attr( $data_date ); ?>">
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
										

					<?php endwhile; ?>

					<?php wp_reset_postdata(); ?>

				<?php endif; ?>

			</ol>
		</div> <!-- .events-content -->
	</section>
<?php else : ?>

<section class="etbi-horizontal-timeline no-events">

	<div class="event-timeline-header">
		<h2 class="upcoming-event-heading">Upcoming Events</h2>

		<?php if( is_user_logged_in() && groups_is_user_member( get_current_user_id(), bp_get_current_group_id() ) ) : ?>

			<form name="register_all_events" class="register-all-events-form" method="POST">
				<input type="hidden" name="group_id" value="<?php echo esc_attr( bp_get_current_group_id() ); ?>" >
				 <?php wp_nonce_field( 'register_all_events_nonce', 'register_all_events_nonce' ); ?>
				<button type="submit" class="button register-all-events-btn" disabled>Register to all events</button>
			</form>

		<?php endif; ?>
	</div>

	<div class="etbi-no-events">
		<p>There are no upcoming events scheduled for this organization.</p>
	</div>
</section>

<?php endif; ?>