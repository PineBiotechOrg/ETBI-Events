<?php
/**
 * WP Events Manager Event Settings meta box view
 *
 * @author        ThimPress, leehld
 * @package       WP-Events-Manager/View
 * @version       2.1.7
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

global $post;
$post_id    = $post->ID;
$prefix     = 'tp_event_';
$date_start = get_post_meta( $post->ID, $prefix . 'date_start', true ) ? date( 'Y-m-d', strtotime( get_post_meta( $post->ID, $prefix . 'date_start', true ) ) ) : '';
$time_start = get_post_meta( $post->ID, $prefix . 'time_start', true ) ? date( 'H:i', strtotime( get_post_meta( $post->ID, $prefix . 'time_start', true ) ) ) : '';

$date_end = get_post_meta( $post->ID, $prefix . 'date_end', true ) ? date( 'Y-m-d', strtotime( get_post_meta( $post->ID, $prefix . 'date_end', true ) ) ) : '';
$time_end = get_post_meta( $post->ID, $prefix . 'time_end', true ) ? date( 'H:i', strtotime( get_post_meta( $post->ID, $prefix . 'time_end', true ) ) ) : '';

$qty      = get_post_meta( $post_id, $prefix . 'qty', true );
$price    = get_post_meta( $post_id, $prefix . 'price', true );
$location = get_post_meta( $post_id, $prefix . 'location', true );
$location_link = get_post_meta( $post_id, $prefix . 'location_link', true );
$program =  ( get_post_meta( $post_id, $prefix . 'program', true ) == 'true' ) ? true : false;
//$program_child_event = get_post_meta( $post_id, $prefix . 'program', true );
$hidden = ( $program == 'true' ) ? '' : 'hidden';
$event_ids_array = (array) maybe_unserialize( get_post_meta( $post_id, $prefix . 'event_ids', true ) );
$exclude_events = $event_ids_array;
$include_events = $event_ids_array;
array_push($exclude_events, $post_id);
$event_ids = implode( ',', $event_ids_array );
$excluded_events = maybe_unserialize( $event_ids );
$events = new WP_Query( array( 'post__not_in' => $exclude_events, 'post_type' => 'tp_event' ) );

// $events = new WP_Query( array( 'post__not_in' => $event_ids_array, 'post_type' => 'tp_event', 'meta_query' => array(
//     'relation' => 'OR', // Optional, defaults to "AND"
//     array(
//         'key'     => '_tp_event_program_parent',
//         'compare' => 'NOT EXISTS'
//     ),
//     array(
//         'relation' => 'AND',
//         array(
//             'key'     => '_tp_event_program_parent',
//             'compare' => 'EXISTS'
//         ),
//         array(
//             'key'     => '_tp_event_program_parent',
//             'value'   => '',
//             'compare' => '='
//         )
//     )
// ) ) );
$selected_events = new WP_Query( array( 'post__in' => $include_events, 'orderby' => 'post__in', 'post_type' => 'tp_event' ) );
$today    = date( "Y-m-d", strtotime( 'today' ) );
$tomorrow = date( "Y-m-d", strtotime( 'tomorrow' ) );
?>
<div class="event_meta_box_container">
    <div class="event_meta_panel">
		<?php do_action( 'tp_event_admin_event_metabox_before_fields', $post, $prefix ); ?>
        <div class="option_group">
            <p class="form-field">
                <label for="_qty"><?php _e( 'Quantity', 'wp-events-manager' ) ?></label>
                <input type="number" min="0" step="1" class="short" name="<?php echo esc_attr( $prefix ) ?>qty" id="_qty" value="<?php echo esc_attr( absint( $qty ) ) ?>">
            </p>
        </div>
        <div class="option_group">
            <p class="form-field">
                <label for="_price"><?php printf( '%s(%s)', __( 'Price', 'wp-events-manager' ), wpems_get_currency_symbol() ) ?></label>
                <input type="number" step="any" min="0" class="short" name="<?php echo esc_attr( $prefix ) ?>price" id="_price" value="<?php echo esc_attr( floatval( $price ) ) ?>" />
            </p>
            <p class="event-meta-notice">
				<?php echo esc_html__( 'Set 0 to make it becomes free event', 'wp-events-manager' ); ?>
            </p>
        </div>

        <div class="option_group">
            <div class="form-field" id="event-time-metabox">
                <label><?php echo esc_html__( 'Start/End', 'wp-events-manager' ); ?></label>
                <label hidden for="_date_start"></label>
                <input type="text" class="short date-start" name="<?php echo esc_attr( $prefix ) ?>date_start" id="_date_start"
                       value="<?php echo $date_start ? esc_attr( $date_start ) : esc_attr( $today ); ?>">
                <label hidden for="_time_start"></label>
                <input type="text" class="short time-start" name="<?php echo esc_attr( $prefix ) ?>time_start" id="_time_start"
                       value="<?php echo $time_start ? esc_attr( $time_start ) : '' ?>">
                <span class="time-connect"> <?php echo esc_html__( 'to', 'wp-events-manager' ); ?></span>
                <label hidden for="_date_end"></label>
                <input type="text" class="short date-end" name="<?php echo esc_attr( $prefix ) ?>date_end" id="_date_end"
                       value="<?php echo $date_end ? esc_attr( $date_end ) : esc_attr( $tomorrow ); ?>">
                <label hidden for="_time_end"></label>
                <input type="text" class="short time-end" name="<?php echo esc_attr( $prefix ) ?>time_end" id="_time_end"
                       value="<?php echo $time_end ? esc_attr( $time_end ) : '' ?>">
            </div>
        </div>
        <div class="option_group">
            <p class="form-field">
                <label for="_location"><?php _e( 'Location', 'wp-events-manager' ) ?></label>
                <input type="text" class="short" name="<?php echo esc_attr( $prefix ) ?>location" id="_location" value="<?php echo esc_attr( $location ) ?>">
            </p>
            <p class="form-field">
                <label for="_location_link"><?php _e( 'Location  link', 'wp-events-manager' ) ?></label>
                <input type="text" class="short" name="<?php echo esc_attr( $prefix )?>location_link" id="_location_link" value="<?php echo esc_attr( $location_link ) ?>">
            </p>
			<?php if ( !wpems_get_option( 'google_map_api_key' ) ): ?>
                <p class="event-meta-notice">
					<?php echo esc_html__( 'You need set up Google Map API Key to show map.', 'wp-events-manager' ); ?>
                    <a href="<?php echo esc_url( get_admin_url() . '/admin.php?page=tp-event-setting&tab=event_general' ); ?>"><?php echo esc_html__( 'Set up here' ) ?></a>
                </p>
			<?php endif; ?>
        </div>
        <div class="option_group">
            <p class="form-field">
                <label for="_program"><?php _e( 'Program', 'wp-events-manager' ) ?></label>
                <input type="checkbox" class="short" name="<?php echo esc_attr( $prefix ) ?>program" id="_program" value="true" <?php echo ( $program ) ? 'checked="checked"' : ''; ?> >
            </p>
            <p class="form-field">
                <input type="hidden" name="<?php echo esc_attr( $prefix ) ?>event_ids" id="_event_ids" value="<?php echo esc_attr( $event_ids ); ?>" >
            </p>


<div id="_program_event_selector" class="ui-widget ui-helper-clearfix row <?php echo esc_attr( $hidden ); ?>">

    <div class="column">
        <h4><?php echo __('All Events'); ?></h3>
        <ul id="all-events" class="all-events ui-helper-reset ui-helper-clearfix connected-sortable">

            <?php if( $events->have_posts() ) : ?>

                    <?php while( $events->have_posts() ) : $events->the_post(); ?>

                        <?php wpems_get_template_part( 'admin', 'event' ); ?>

                     <?php endwhile; ?>

                     <?php wp_reset_postdata(); ?>

             <?php endif; ?>

        </ul>    
    </div>

     
    <div id="program-events" class="ui-widget-content ui-state-default column">
      <h4 class="ui-widget-header"><?php echo __( 'Program events' ); ?></h4>

      <ul id="selected-program-events" class="selected-program-events ui-helper-reset connected-sortable" >

            <?php if( $selected_events->have_posts() ) : ?>

                <?php while( $selected_events->have_posts() ) : $selected_events->the_post(); ?>

                    <?php wpems_get_template_part( 'admin', 'event' ); ?>

                 <?php endwhile; ?>

                 <?php wp_reset_postdata(); ?>

             <?php endif; ?>

        </ul>

    </div>
 
</div>

        </div>
        <div class="option_group">
            <p class="form-field">
                <label for="_shortcode"><?php _e( 'Shortcode', 'wp-events-manager' ) ?></label>
                <input type="text" class="short" id="_shortcode" value="<?php echo esc_attr( '[wp_event_countdown event_id="' . $post->ID . '"]' ); ?>" readonly>
            </p>
        </div>
		<?php wp_nonce_field( 'event_nonce', 'event-nonce' ); ?>
		<?php do_action( 'tp_event_admin_event_metabox_after_fields', $post, $prefix ); ?>
    </div>

  <script>
  $( function() {
 
    // There's the gallery and the trash
    var $all_events = $( "#all-events" ),
      $program_events = $( "#program-events" ),
      $event_ids = [],
      $event_ids_field = $( '#_event_ids' );
 
    // // Let the gallery items be draggable
    // $( "li", $all_events ).draggable({
    //   cancel: "a.ui-icon", // clicking an icon won't initiate dragging
    //   revert: "invalid", // when not dropped, the item will revert back to its initial position
    //   containment: "document",
    //   helper: "clone",
    //   cursor: "move"
    // });

    // $( "li", $program_events ).draggable({
    //   cancel: "a.ui-icon", // clicking an icon won't initiate dragging
    //   revert: "invalid", // when not dropped, the item will revert back to its initial position
    //   containment: "document",
    //   helper: "clone",
    //   cursor: "move"
    // });

    $( "#selected-program-events, #all-events" ).sortable({
        items: '> li.event-item',
        connectWith: ".connected-sortable",
        receive: function(event, ui) {
            var events = new Array();
            $("#selected-program-events > li.event-item").each(function() {
                events.push($(this).attr("data-id"));
            });
            $event_ids_field.val( events );
            console.log(events.toString());  
        },
        remove: function( event, ui ) {
            var events = new Array();
            $("#selected-program-events > li.event-item").each(function() {
                events.push($(this).attr("data-id"));
            });
            $event_ids_field.val( events );
            console.log(events.toString());  

        },
        update: function( event, ui ) {
            var events = new Array();
            $("#selected-program-events > li.event-item").each(function() {
                events.push($(this).attr("data-id"));
            });
            $event_ids_field.val( events );
            console.log(events.toString());         
        }

    }).disableSelection();
 
    // // Let the trash be droppable, accepting the gallery items
    // $program_events.droppable({
    //   accept: "#all-events > li",
    //   classes: {
    //     "ui-droppable-active": "ui-state-highlight"
    //   },
    //   drop: function( event, ui ) {
    //     deleteImage( ui.draggable );
    //     addEvent( ui.draggable );
    //   }
    // });
 
    // // Let the gallery be droppable as well, accepting items from the trash
    // $all_events.droppable({
    //   accept: "#program-events li",
    //   classes: {
    //     "ui-droppable-active": "custom-state-active"
    //   },
    //   drop: function( event, ui ) {
    //     recycleImage( ui.draggable );
    //     removeEvent( ui.draggable );
    //   }
    // });

    function addEvent( $item ) {
        var $selected_events = $( '.selected-program-events .event-id' );

            $event_ids.push( $item.find( '.event-id' ).val() );
            console.log( $event_ids );
            $event_ids_field.val( $event_ids );

    }

    function removeEvent( $item ) {
        var $selected_events = $( '.selected-program-events .event-id' );

        $event_ids = $.grep( $event_ids, function( event_id ) {

            return event_id !== $item.find( '.event-id' ).val();

        } );

        //$event_ids.push( $item.find( '.event-id' ).val() );
        console.log( $event_ids );
        $event_ids_field.val( $event_ids );

    }
 
    // Image deletion function
    var recycle_icon = "<a href='link/to/recycle/script/when/we/have/js/off' title='Recycle this image' class='ui-icon ui-icon-refresh'>Recycle image</a>";
    function deleteImage( $item ) {
      $item.fadeOut(function() {
        var $list = $( "ul", $program_events ).length ?
          $( "ul", $program_events ) :
          $( "<ul id='selected-program-events' class='selected-program-events ui-helper-reset'/>" ).appendTo( $program_events );
 
        $item.find( "a.ui-icon-trash" ).remove();
        $item.append( recycle_icon ).appendTo( $list ).fadeIn(function() {
          // $item
          //   .animate({ width: "48px" })
          //   .find( "img" )
          //     .animate({ height: "36px" });
        });
      });
    }
 
    // Image recycle function
    var trash_icon = "<a href='link/to/trash/script/when/we/have/js/off' title='Delete this image' class='ui-icon ui-icon-trash'>Delete image</a>";
    function recycleImage( $item ) {
      $item.fadeOut(function() {
        $item
          .find( "a.ui-icon-refresh" )
            .remove()
          .end()
          .append( trash_icon )
          .find( "img" )
          .end()
          .appendTo( $all_events )
          .fadeIn();
      });
    }
 
    // Image preview function, demonstrating the ui.dialog used as a modal window
    function viewLargerImage( $link ) {
      var src = $link.attr( "href" ),
        title = $link.siblings( "img" ).attr( "alt" ),
        $modal = $( "img[src$='" + src + "']" );
 
      if ( $modal.length ) {
        $modal.dialog( "open" );
      } else {
        var img = $( "<img alt='" + title + "' width='384' height='288' style='display: none; padding: 8px;' />" )
          .attr( "src", src ).appendTo( "body" );
        setTimeout(function() {
          img.dialog({
            title: title,
            width: 400,
            modal: true
          });
        }, 1 );
      }
    }
 
    // Resolve the icons behavior with event delegation
    $( "ul.all-events > li" ).on( "click", function( event ) {
      var $item = $( this ),
        $target = $( event.target );
 
      if ( $target.is( "a.ui-icon-trash" ) ) {
        deleteImage( $item );
      } else if ( $target.is( "a.ui-icon-zoomin" ) ) {
        viewLargerImage( $target );
      } else if ( $target.is( "a.ui-icon-refresh" ) ) {
        recycleImage( $item );
      }
 
      return false;
    });
  } );
  </script>
</div>