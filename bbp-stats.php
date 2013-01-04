<?php
/*
Plugin Name: bbPress - Stats
Plugin URI: http://pippinsplugins.com/bbpress-stats
Description: Statistic graphs for bbPress
Version: 1.0
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Contributors: mordauk
*/


class PW_BBP_Stats {

	/**
	 * @var bbp Admin Notes instance
	 */

	private static $instance;


	/**
	 * Main class instance
	 *
	 * @since v1.0
	 *
	 * @return the class instance
	 */

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PW_BBP_Stats;
			self::$instance->actions();
			self::$instance->filters();
		}
		return self::$instance;
	}


	/**
	 * Dummy constructor
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	private function __construct() { /* nothing here */ }


	/**
	 * Add all actions we need
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	private function actions() {
		add_action( 'admin_menu', array( $this, 'forums_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}


	/**
	 * Add all filters we need
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	private function filters() {

	}


	/**
	 * Register our Forums menu
	 *
	 * @since v1.0
	 *
	 * @return void
	 */
	public function forums_menu() {
		add_submenu_page(
			'edit.php?post_type=' . bbp_get_forum_post_type(),
			__( 'Stats', 'bbp-stats' ),
			__( 'Stats', 'bbp-stats' ),
			'publish_forums',
			'bbp-stats',
			array( __CLASS__, 'stats_page' )
		);
	}


	/**
	 * Render the stats page
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	public function stats_page() {

		$dates = self::graph_dates();

		// Determine graph options
		switch( $dates['range'] ) :
			case 'last_year' :
				$time_format 	= '%b';
				$tick_size		= 'month';
				$day_by_day		= false;
				break;
			case 'this_year' :
				$time_format 	= '%b';
				$tick_size		= 'month';
				$day_by_day		= false;
				break;
			case 'last_quarter' :
				$time_format	= '%b';
				$tick_size		= 'month';
				$day_by_day 	= false;
				break;
			case 'this_quarter' :
				$time_format	= '%b';
				$tick_size		= 'month';
				$day_by_day 	= false;
				break;
			case 'other' :
				if( ( $dates['m_end'] - $dates['m_start'] ) >= 2 ) {
					$time_format	= '%b';
					$tick_size		= 'month';
					$day_by_day 	= false;
				} else {
					$time_format 	= '%d/%b';
					$tick_size		= 'day';
					$day_by_day 	= true;
				}
				break;
			default:
				$time_format 	= '%d/%b'; 	// Show days by default
				$tick_size		= 'day'; 	// Default graph interval
				$day_by_day 	= true;
				break;
		endswitch;
		$time_format 	= apply_filters( 'bbp_stats_graph_timeformat', $time_format );
		$tick_size 		= apply_filters( 'bpp_stats_graph_ticksize', $tick_size );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'bbPress Stats', 'bbp-stats' ); ?></h2>
			<?php self::bpp_stats_controls(); ?>
			<script type="text/javascript">
			   	jQuery( document ).ready( function($) {
			   		$( '#bpp-stats-date-options' ).change( function() {
						var $this = $(this);
						if( $this.val() == 'other' ) {
							$( '#bpp-stats-date-range-options' ).show();
						} else {
							$( '#bpp-stats-date-range-options' ).hide();
						}
					});
			   		$.plot(
			   			$("#bbp_topic_stats"),
			   			[{
		   					data: [
			   					<?php
			   					$i = $dates['m_start'];
								while ( $i <= $dates['m_end'] ) :
									if ( $day_by_day ) :
										$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
										$d 				= 1;
										while ( $d <= $num_of_days ) :
											$date = mktime( 0, 0, 0, $i, $d, $dates['year'] ); ?>
											[<?php echo $date * 1000; ?>, <?php echo self::topics_by_date( $d, $i, $dates['year'] ); ?>],
										<?php
										$d++;
										endwhile;
									else :
										$date = mktime( 0, 0, 0, $i, 1, $dates['year'] ); ?>
										[<?php echo $date * 1000; ?>, <?php echo self::topics_by_date( null, $i, $dates['year'] ); ?>],
									<?php
									endif;
									$i++;
								endwhile;
			   					?>,
			   				]
		   				}],
			   			{
			               	series: {
			                   lines: { show: true },
			                   points: { show: true }
			            	},
			            	grid: {
			           			show: true,
								aboveData: false,
								color: '#ccc',
								backgroundColor: '#fff',
								borderWidth: 2,
								borderColor: '#ccc',
								clickable: false,
								hoverable: true
			           		},
			            	xaxis: {
				   				mode: "time",
				   				timeFormat: "<?php echo $time_format; ?>",
				   				minTickSize: [1, "<?php echo $tick_size; ?>"]
			   				},
			   				yaxis: [
			   					{ min: 0, tickSize: 1, tickDecimals: 2 },
			   					{ min: 0, tickDecimals: 0 }
			   				]
		            	}
		        	);

					$.plot(
			   			$("#bbp_reply_stats"),
			   			[{
		   					data: [
			   					<?php
			   					$i = $dates['m_start'];
								while ( $i <= $dates['m_end'] ) :
									if ( $day_by_day ) :
										$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
										$d 				= 1;
										while ( $d <= $num_of_days ) :
											$date = mktime( 0, 0, 0, $i, $d, $dates['year'] ); ?>
											[<?php echo $date * 1000; ?>, <?php echo self::replies_by_date( $d, $i, $dates['year'] ); ?>],
										<?php
										$d++;
										endwhile;
									else :
										$date = mktime( 0, 0, 0, $i, 1, $dates['year'] ); ?>
										[<?php echo $date * 1000; ?>, <?php echo self::replies_by_date( null, $i, $dates['year'] ); ?>],
									<?php
									endif;
									$i++;
								endwhile;
			   					?>,
			   				]
		   				}],
			   			{
			               	series: {
			                   lines: { show: true },
			                   points: { show: true }
			            	},
			            	grid: {
			           			show: true,
								aboveData: false,
								color: '#ccc',
								backgroundColor: '#fff',
								borderWidth: 2,
								borderColor: '#ccc',
								clickable: false,
								hoverable: true
			           		},
			            	xaxis: {
				   				mode: "time",
				   				timeFormat: "<?php echo $time_format; ?>",
				   				minTickSize: [1, "<?php echo $tick_size; ?>"]
			   				},
			   				yaxis: [
			   					{ min: 0, tickSize: 1, tickDecimals: 2 },
			   					{ min: 0, tickDecimals: 0 }
			   				]
		            	}
		        	);
					function bbp_stats_flot_tooltip(x, y, contents) {
				        $('<div id="bbp-stats-flot-tooltip">' + contents + '</div>').css( {
				            position: 'absolute',
				            display: 'none',
				            top: y + 5,
				            left: x + 5,
				            border: '1px solid #fdd',
				            padding: '2px',
				            'background-color': '#fee',
				            opacity: 0.80
				        }).appendTo("body").fadeIn(200);
				    }

				    var previousPoint = null;
				    $("#bbp_topic_stats,#bbp_reply_stats").bind("plothover", function (event, pos, item) {
				        $("#x").text(pos.x.toFixed(2));
				        $("#y").text(pos.y.toFixed(2));
			            if (item) {
			                if (previousPoint != item.dataIndex) {
			                    previousPoint = item.dataIndex;
			                    $("#bbp-stats-flot-tooltip").remove();
			                    var x = item.datapoint[0].toFixed(2),
			                        y = item.datapoint[1].toFixed(2);

				                bbp_stats_flot_tooltip( item.pageX, item.pageY, y.replace( '.00', '' ) );

			                }
			            } else {
			                $("#bbp-stats-flot-tooltip").remove();
			                previousPoint = null;
			            }
				    });
			   });
		    </script>
		    <h3><?php _e( 'Topics Created', 'bbp-stats' ); ?></h3>
			<div id="bbp_topic_stats" style="height: 300px;"></div>
			<h3><?php _e( 'Replies Posted', 'bbp-stats' ); ?></h3>
			<div id="bbp_reply_stats" style="height: 300px;"></div>
		</div>
		<?php
	}


	/**
	 * Load our JS scripts
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	public function scripts( $hook ) {

		if( 'forum_page_bbp-stats' != $hook )
			return;

		wp_enqueue_script( 'jquery-flot', plugins_url( 'assets/js/jquery.flot.js' , __FILE__ ), array( 'jquery' ), filemtime( __FILE__ ) );
	}


	/**
	 * Retrieve topics by date
	 *
	 * @since v1.0
	 *
	 * @return int
	 */

	private function topics_by_date( $day = null, $month = null, $year = null ) {
		$args = array(
			'post_type'      => bbp_get_topic_post_type(),
			'posts_per_page' => -1,
			'year'           => $year,
			'monthnum'       => $month,
			'post_status'    => 'publish',
			'no_found_rows'  => false,
			'cache_results'  => false,
			'fields'         => 'ids'
		);
		if ( !empty( $day ) )
			$args['day'] = $day;

		$topics = new WP_Query( $args );

		return (int) $topics->post_count;
	}


	/**
	 * Retrieve replies by date
	 *
	 * @since v1.0
	 *
	 * @return int
	 */

	private function replies_by_date( $day = null, $month = null, $year = null ) {
		$args = array(
			'post_type'      => bbp_get_reply_post_type(),
			'posts_per_page' => -1,
			'year'           => $year,
			'monthnum'       => $month,
			'post_status'    => 'publish'
		);
		if ( !empty( $day ) )
			$args['day'] = $day;

		$topics = new WP_Query( $args );

		return (int) $topics->post_count;
	}


	/**
	 * Stat date controls
	 *
	 * @since v1.0
	 *
	 * @return void
	 */

	private function bpp_stats_controls() {
		$date_options = apply_filters( 'bbp_stats_date_options', array(
			'this_month'    => __( 'This Month', 'bpp-stats' ),
			'last_month'    => __( 'Last Month', 'bpp-stats' ),
			'this_quarter'  => __( 'This Quarter', 'bpp-stats' ),
			'last_quarter'  => __( 'Last Quarter', 'bpp-stats' ),
			'this_year'     => __( 'This Year', 'bpp-stats' ),
			'last_year'     => __( 'Last Year', 'bpp-stats' ),
			'other'         => __( 'Other', 'bpp-stats' )
		) );

		$dates = self::graph_dates();

		$display = $dates['range'] == 'other' ? '' : 'style="display:none;"';

		?>
		<form id="bpp-stats-graphs-filter" method="get">
			<div class="tablenav top">
		       	<input type="hidden" name="post_type" value="<?php echo bbp_get_forum_post_type(); ?>"/>
		       	<input type="hidden" name="page" value="bbp-stats"/>

		       	<select id="bpp-stats-date-options" name="range">
		       		<?php
		       		foreach ( $date_options as $key => $option ) {
		       			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $dates['range'] ) . '>' . esc_html( $option ) . '</option>';
		       		}
		       		?>
		       	</select>

		       	<span id="bpp-stats-date-range-options" <?php echo $display; ?>>
					&mdash;
				    <span><?php _e( 'From', 'bpp-stats' ); ?>&nbsp;</span>
			       	<select id="bpp-stats-month-start" name="m_start">
			       		<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_start'] ); ?>><?php echo self::bbp_stats_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<span><?php _e( 'To', 'bpp-stats' ); ?>&nbsp;</span>
			       	<select id="bpp-stats-month-start" name="m_end">
			       		<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['m_end'] ); ?>><?php echo self::bbp_stats_month_num_to_name( $i ); ?></option>
				       	<?php endfor; ?>
			       	</select>
			       	<select id="bpp-stats-year" name="year">
			       		<?php for ( $i = 2007; $i <= $dates['year_end']; $i++ ) : ?>
			       			<option value="<?php echo absint( $i ); ?>" <?php selected( $i, $dates['year'] ); ?>><?php echo $i; ?></option>
				       	<?php endfor; ?>
			       	</select>
			    </span>

		       	<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'bpp-stats' ); ?>"/>
			</div>
		</form>
		<?php
	}


	/**
	 * Retrieve dates query parameters
	 *
	 * @since v1.0
	 *
	 * @return array
	 */

	private function graph_dates() {

		$dates = array();

		$dates['range']    = isset( $_GET['range'] )   ? $_GET['range']   : 'this_month';
		$dates['day']      = isset( $_GET['day'] )     ? $_GET['day']     : null;
		$dates['m_start']  = isset( $_GET['m_start'] ) ? $_GET['m_start'] : 1;
		$dates['m_end']    = isset( $_GET['m_end'] )   ? $_GET['m_end']   : 12;
		$dates['year']     = isset( $_GET['year'] )    ? $_GET['year']    : date( 'Y' );
		$dates['year_end'] = date( 'Y' );

		// Modify dates based on predefined ranges
		switch( $dates['range'] ) :

			case 'this_month' :

				$dates['m_start']   = date( 'n' );
				$dates['m_end']     = date( 'n' );
				$dates['year']      = date( 'Y' );

				break;

			case 'last_month' :

				$dates['m_start']   = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
				$dates['m_end']     = date( 'n' ) == 1 ? 12 : date( 'n' ) - 1;
				$dates['year']      = date( 'n' ) == 1 ? date( 'Y' ) - 1 : date( 'Y' );

				break;

			case 'this_quarter' :

				$month_now = date( 'n' );

				if ( $month_now <= 3 ) {

					$dates['m_start']   = 1;
					$dates['m_end']     = 3;
					$dates['year']      = date( 'Y' );

				} else if ( $month_now <= 6 ) {

					$dates['m_start']   = 4;
					$dates['m_end']     = 6;
					$dates['year']      = date( 'Y' );

				} else if ( $month_now <= 9 ) {

					$dates['m_start']   = 7;
					$dates['m_end']     = 9;
					$dates['year']      = date( 'Y' );

				} else {

					$dates['m_start']   = 10;
					$dates['m_end']     = 12;
					$dates['year']      = date( 'Y' );

				}

				break;

			case 'last_quarter' :

				$month_now = date( 'n' );

				if ( $month_now <= 3 ) {

					$dates['m_start']   = 10;
					$dates['m_end']     = 12;
					$dates['year']      = date( 'Y' ) - 1; // Previous year

				} else if ( $month_now <= 6 ) {

					$dates['m_start']   = 1;
					$dates['m_end']     = 3;
					$dates['year']      = date( 'Y' );

				} else if ( $month_now <= 9 ) {

					$dates['m_start']   = 4;
					$dates['m_end']     = 6;
					$dates['year']      = date( 'Y' );

				} else {

					$dates['m_start']   = 7;
					$dates['m_end']     = 9;
					$dates['year']      = date( 'Y' );

				}

				break;

			case 'this_year' :

				$dates['m_start']       = 1;
				$dates['m_end']         = 12;
				$dates['year']          = date( 'Y' );

				break;

			case 'last_year' :

				$dates['m_start']       = 1;
				$dates['m_end']         = 12;
				$dates['year']          = date( 'Y' ) - 1;

				break;

		endswitch;

		return apply_filters( 'bbp_stats_graph_dates', $dates );
	}


	/**
	 * Convert a month number to the corresponding month name
	 *
	 * @since v1.0
	 *
	 * @return string
	 */

	private function bbp_stats_month_num_to_name( $n ) {
		$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

		return date_i18n( "M", $timestamp );
	}

}


/**
 * Load our singleton class
 *
 * @since v1.0
 *
 * @return void
 */

function pw_bbp_stats() {
	return PW_BBP_Stats::instance();
}

// Load the class
pw_bbp_stats();