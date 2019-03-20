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

$event_ids_array = maybe_unserialize( groups_get_groupmeta( $group_id, '_etbi_group_event_ids', true ) );
$event_ids = '';

if( is_array( $event_ids_array ) && ! empty( $event_ids_array ) ) {

$event_ids = implode( ',', $event_ids_array );
$all_events_args = array( 'post__not_in' => $event_ids_array, 'post_type' => 'tp_event', 'meta_query' => array(
    'relation' => 'OR',
    array(
        'key'     => '_tp_event_program_parent',
        'compare' => 'NOT EXISTS'
    ),
    array(
        'relation' => 'AND',
        array(
            'key'     => '_tp_event_program_parent',
            'compare' => 'EXISTS'
        ),
        array(
            'key'     => '_tp_event_program_parent',
            'value'   => '',
            'compare' => '='
        )
    )
) );

$selected_events_args = array( 'post__in' => $event_ids_array, 'orderby' => 'post__in', 'post_type' => 'tp_event' );


} else {

$all_events_args = array('post_type' => 'tp_event', 'meta_query' => array(
    'relation' => 'OR',
    array(
        'key'     => '_tp_event_program_parent',
        'compare' => 'NOT EXISTS'
    ),
    array(
        'relation' => 'AND',
        array(
            'key'     => '_tp_event_program_parent',
            'compare' => 'EXISTS'
        ),
        array(
            'key'     => '_tp_event_program_parent',
            'value'   => '',
            'compare' => '='
        )
    )
) );

//$selected_events_args = array( 'post__in' => $event_ids_array, 'orderby' => 'post__in', 'post_type' => 'tp_event' );

}

$events = new WP_Query( $all_events_args );
$selected_events = new WP_Query( $selected_events_args );

?>
<div class="event_meta_box_container">

        <div class="option_group">

            <p class="form-field">
                <input type="hidden" name="_etbi_group_event_ids" id="_event_ids" value="<?php echo esc_attr( $event_ids ); ?>" >
            </p>


            <div id="_program_event_selector" class="ui-widget ui-helper-clearfix row ">

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

                 
                <div id="group-events" class="ui-widget-content ui-state-default column">
                  <h4 class="group-events-header"><?php echo __( 'Group events' ); ?></h4>

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
		<?php wp_nonce_field( 'event_nonce', 'event-nonce' ); ?>
		
    </div>

  <script>
  $( function() {
 
    // There's the gallery and the trash
    var $all_events = $( "#all-events" ),
      $program_events = $( "#group-events" ),
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