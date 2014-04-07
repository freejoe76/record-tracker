<?php
/**
 * Plugin Name: Rockies Win-O-Meter
 * Plugin URI: https://gist.github.com/freejoe76/ff90b3a0f16f33a44e43 
 * Descriphtmltion: Publish a thermometer tracking the Colorado Rockies' wins in the 2014 MLB season.
 * Version: 0.1
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

        $team = $object->{'team-sport-content'}[0]->{'league-content'}[0]->{'season-content'}[0]->{'conference-content'}[0]->{'division-content'}[1]->{'team-content'}[1];
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

function runit()
{
    // I know, 'runit' is a bad idea for a function, but
    // it's required by wp_schedule_update.
    $update = new UpdateData();
    $update->get_xml();
    $update->write_xml();
    $data = $update->parse_xml();
    $update->xml_to_json($data['season'], 'season.json');
}

// We do this to get the Rockies data ingested every hour.
// On an early action hook, check if the hook is scheduled - if not, schedule it.
add_action( 'wp', 'prefix_setup_schedule' );
function prefix_setup_schedule() 
{
    if ( ! wp_next_scheduled( 'prefix_hourly_event' ) ):
        wp_schedule_event(time(), 'hourly', 'prefix_hourly_event');
    endif;
}
add_action( 'prefix_hourly_event', 'runit' );


class sidebar_thermometer extends WP_Widget
{
    public function __construct()
    {
            parent::__construct(
                'sidebar_thermometer',
                __('Rockies Win-O-Meter', 'sidebar_thermometer'),
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

// Code to create the page. Right now the page slug is hard-coded.
// Note: You've got to create a page with the slug "wins" for this to work.
add_filter( 'template_include', 'thermometer_page_template', 99 );
function thermometer_page_template( $template )
{
    if ( is_page( 'wins' ) ):
        $template = dirname( __FILE__ ) . '/page.php';
    endif;
    return $template;
}
?>
