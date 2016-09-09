<?php
// Download XML file, write a json file.
class UpdateRecordData
{
    var $xml;
    var $url;
    var $test;
    var $path_prefix;

    function __construct()
    {
        $this->url = 'http://xml.sportsdirectinc.com/sport/v2/baseball/MLB/standings/2016/standings_MLB.xml';
        $this->test = true;
        $this->path_prefix = '';
    }

    public function get_xml()
    {
        // Get the XML
        if ( $this->test == true ):
            $this->url = $this->path_prefix . 'tmp.xml';
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
            return file_put_contents($this->path_prefix . 'tmp.xml', $this->xml);
        endif;
        return false;
    }

    public function parse_xml()
    {
        // Take the xml and pull out the chunk that we want.
        // Will have to build this method out if we want to measure things
        // in addition to the Rockies wins.
        if ( $this->xml === false ):
            $this->xml = $this->get_xml();
        endif;
        $object = simplexml_load_string($this->xml);

        $conferences = $object->{'team-sport-content'}[0]->{'league-content'}[0]->{'season-content'}[0];

        // Loops, loops.
        // This is, I swear, the most elegant way of getting the data element.
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

$update = new UpdateRecordData();
$update->path_prefix = 'output/';
$update->test = false;
$update->get_xml();
$update->write_xml();
$data = $update->parse_xml();
$update->xml_to_json($data['season'], 'season.json');
