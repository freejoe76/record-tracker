<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>90 Wins</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>
<body>
<!-- ``bookmark`` -->
<style type="text/css" media="screen">
/* Thermometer column and text */
 .thermometer {
    margin:50% 0 0 50%;
    left:-15px;
    top:-500px;
    width:22px;
    height:150px;
    display:block;
    font:bold 14px/152px helvetica, arial, sans-serif;
    text-indent: 36px;
    background: -webkit-linear-gradient(top, #fff 0%, #fff 100%, #db3f02 100%, #db3f02 100%);
    border-radius:22px 22px 0 0;
    border:5px solid #4a1c03;
    border-bottom:none;
    position:absolute;
    box-shadow:inset 0 0 0 4px #fff;
    color:#4a1c03;
}
/* Thermometer Bulb */
 .thermometer:before {
    content:' ';
    width:44px;
    height:44px;
    display:block;
    position:absolute;
    top:142px;
    left:-16px;
    z-index:-1;
    /* Place the bulb under the column */
    background:#db3f02;
    border-radius:44px;
    border:5px solid #4a1c03;
    box-shadow:inset 0 0 0 4px #fff;
}
/* This piece here connects the column with the bulb */
 .thermometer:after {
    content:' ';
    width:14px;
    height:7px;
    display:block;
    position:absolute;
    top:146px;
    left:4px;
    background:#db3f02;
}
 .thermo_label {
    text-indent:0;
    font:bold 14px/20px helvetica, arial, sans-serif;
    width:200px;
    left:30px;
    position: absolute;
    display:block;
}
.thermo_rate, .thermo_seasons, #credits { font-weight: normal; }
</style>
<?php
$path = '';
if ( function_exists('plugin_dir_path') ):
    $path .= plugin_dir_path( __FILE__ );
endif;
$path .= 'season.json';
$json = file_get_contents($path);
$json_object = json_decode($json, true);
$stats = array();
foreach ( $json_object['stat'] as $item ):
    $stats[$item['@attributes']['type']] = $item['@attributes']['num'];
endforeach;
/*
array(5) {
  ["games_won"]=>
  string(1) "1"
  ["games_lost"]=>
  string(1) "2"
  ["win_streak"]=>
  string(1) "1"
  ["runs_for"]=>
  string(2) "10"
  ["runs_against"]=>
  string(2) "19"
}
*/
?>
<span class="thermometer">
    <span class="thermo_label" id="thermo-text">
        <span id="headline">78 wins until 90.</span><br>
        <span id="wins"><?php echo $stats['games_won']; ?></span> wins, <span id="losses"><?php echo $stats['games_lost']; ?></span> losses.<br><br>
        <span class="thermo_rate">At this rate, the Rockies will win <span id="rate">65</span> games,</span>
        <span class="thermo_seasons">and it will take <span id="seasons">1.4 seasons</span> to win 90.</span><br>
        <span id="credits">
            <br>&nbsp;&nbsp;<a href="http://www.denverpost.com/kiszla/ci_25428848/kiszla-rockies-can-win-90-games-according-dick-monfort"><em>Inspired by Dick Monfort</em></a>,
            <br>&nbsp;&nbsp;&nbsp;<em>code by <a href="http://twitter.com/digitalfirstjoe">Joe Murphy</a>.</em>
        </span>
    </span>
</span>
<script type="text/javascript">
var thermo = {
    season: 162,
    wins: <?php echo $stats['games_won']; ?>,
    losses: <?php echo $stats['games_lost']; ?>,
    wins_goal: 90,
    games_played: function calculate_games_played() 
    {
        return this.wins + this.losses;
    },
    games_left: function calculate_games_left()
    {
        return this.season - this.games_played();
    },
    games_to_win: function calculate_to_win()
    {
        return this.wins_goal - this.wins;
    },
    win_rate: function calculate_rate() 
    {
        if ( this.games_played() == 0 ) return 0;
        if ( this.wins == 0 && this.losses > 0 ) return '∞';
        return this.wins / this.games_played();
    },
    percent_won: function calculate_percent_won() 
    {
        if ( typeof this.win_rate() == 'string' ) return 'ZERO';
        return this.wins / this.wins_goal;
    },
    projected_wins: function calculate_projected_wins() 
    {
        if ( typeof this.win_rate() == 'string' ) return 'ZERO';
        return Math.round(this.win_rate() * this.games_left());
    },
    seasons: function calculate_seasons() 
    {
        if ( typeof this.win_rate() == 'string' ) return 'FOREVER';
        //return this.win_rate() * x / this.season;
         
    },
    init: function init()
    {
        if ( typeof($) != 'undefined' )
        {
            $('#headline').text(this.games_to_win() + " wins until " + this.wins_goal + ".");
            $('#wins').text(this.wins);
            $('#losses').text(this.losses);
            $('#rate').text(this.projected_wins());
            //$('#rate').text(this.projected_wins());
            var percent = 100 - this.percent_won();       
            $('.thermometer').css('background', '-webkit-linear-gradient(top, #fff 0%, #fff ' + percent + '%, #db3f02 ' + percent + '%, #db3f02 100%)');
        }
    }
};
thermo.init();
</script>
<!-- ``bookmark`` -->
</body>
</html>
