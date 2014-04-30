<?php
/**
 * Plugin Name: Rockies Record Tracker
 * Plugin URI: https://github.com/freejoe76/record-tracker
 * Description: Publish a thermometer tracking the Colorado Rockies' record in the 2014 MLB season.
 * Version: 0.2
 * Author: Joe Murphy
 * Author URI: http://joemurph.com/
 * License: Apache-2
 */

class UpdateData
{

    var $xml;
    var $url;
    var $test;
    var $path_prefix;

    function __construct()
    {
        $this->url = 'http://xml.sportsdirectinc.com/sport/v2/baseball/MLB/standings/2014/standings_MLB.xml';
        $this->test = true;
        $this->path_prefix = '';
        if ( function_exists('plugin_dir_path') ):
            $this->path_prefix = plugin_dir_path( __FILE__ );
        endif;
    }

    public function set_test($test)
    {
        // Set the value of the test var.
        return $this->test = $test;
    }

    public function get_xml()
    {
        // Get the XML
        if ( $this->test == true ):
            $this->url = $this->path_prefix . 'updates.xml';
        endif;

        $this->xml = file_get_contents($this->url);
        return $this->xml;
    }

    public function write_xml()
    {
        if ( $this->xml === false ):
            $this->xml = $this->get_xml();
        endif;
        if ( $this->xml != false ):
            return file_put_contents($this->path_prefix . 'updates.xml', $this->xml);
        endif;
        return false;
    }

    public function parse_xml()
    {
        // Take the xml in updates.xml and pull out the chunk that we want.
        // Will have to build this method out if we want to measure things
        // in addition to the Rockies wins.
        if ( $this->xml === false ):
            $this->xml = $this->get_xml();
        endif;
        $object = simplexml_load_string($this->xml);

        $conferences = $object->{'team-sport-content'}[0]->{'league-content'}[0]->{'season-content'}[0];

        // Loops, loops.
        // This is, I swear, the most elegant way of getting the Rockies' element.
        foreach ( $conferences->children() as $conference):
            if ( $conference->conference->name == 'National League' ):
                foreach ( $conference->children() as $division):
                    if ( $division->division->name == 'National League West' ):
                        foreach ( $division->children() as $teams ):
                            if ( $teams->team->name == 'Colorado' ):
                                $team = $teams;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;
        endforeach;
        $regular_season = $team->{'stat-group'}[1];
        $last_ten = $team->{'stat-group'}[3];
        $data = array('season' => $regular_season, 'last_ten' => $last_ten);
        return $data;
    }

    public function xml_to_json($xml, $filename)
    {
        $json = json_encode($xml);
        return file_put_contents($this->path_prefix . $filename, $json);
    }
}

function runit($test=false)
{
    // I know, 'runit' is a bad idea for a function, but
    // it's required by wp_schedule_update.
    $update = new UpdateData();
    $update->set_test($test);
    $update->get_xml();
    $update->write_xml();
    $data = $update->parse_xml();
    $update->xml_to_json($data['season'], 'season.json');
}

// *******************
//
// CRONTAB
//
// *******************
// We do this to get the Rockies data ingested every hour.
// On an early action hook, check if the hook is scheduled - if not, schedule it.
if ( function_exists('add_action') ):
add_action( 'wp', 'prefix_setup_schedule' );
function prefix_setup_schedule() 
{
    if ( ! wp_next_scheduled( 'prefix_hourly_event' ) ):
        wp_schedule_event(time(), 'hourly', 'prefix_hourly_event');
    endif;
}
add_action( 'prefix_hourly_event', 'runit' );
endif;


// *******************
//
// COMMAND-LINE USE
//
// *******************
// If we're running this file from the command line, we want to run this script.
if ( isset($_SERVER['argv'][0]) ):
    if ( isset($_SERVER['argv'][1]) ):
        // Not testing, will d/l file from Sports Direct
        // To run it this way:
        // $ php the_thermometer.php notest
        runit(false);
    else:
        // Testing, will use local files.
        // $ php the_thermometer.php
        runit(true);
    endif;
endif;


// *******************
//
// WIDGET
//
// *******************
if ( class_exists('WP_Widget') ):
class sidebar_thermometer extends WP_Widget
{
    public function __construct()
    {
            parent::__construct(
                'sidebar_thermometer',
                __('Rockies Record Tracker', 'sidebar_thermometer'),
                array('description' => __('Publish a thermometer that tracks how close the Rockies are to 90 wins.', 'sidebar_thermometer'), )
            );
    }

    public function widget($args, $instance)
    {
        // 
        $path_prefix = '';
        if ( function_exists('plugin_dir_path') ):
            $path_prefix = plugin_dir_path( __FILE__ );
        endif;
        echo '
            <!-- ##THERMOMETER## -->
            ';
        include($path_prefix . 'template-widget.php');
        echo '    <!-- ##ENDTHERMOMETER## -->';
        }
}

function register_thermometer_widget() { register_widget('sidebar_thermometer'); }
add_action( 'widgets_init', 'register_thermometer_widget' );
endif;



// *******************
//
// PAGE
//
// *******************
// Code to create the page. Right now the page slug is hard-coded.
// Note: You've got to create a page with the slug "wins" for this to work.
if ( function_exists('add_filter') ):
add_filter( 'template_include', 'thermometer_page_template', 99 );
function thermometer_page_template( $template )
{
    if ( is_page( 'recordtracker' ) ):
        $template = dirname( __FILE__ ) . '/page.php';
    elseif ( is_page( 'recordtracker-include' ) ):
        $template = dirname( __FILE__ ) . '/page-iframe.php';
    endif;
    return $template;
}
endif;
?>
