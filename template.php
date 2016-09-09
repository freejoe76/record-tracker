<?php ob_start(); ?><!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Record Tracker</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <style type="text/css" media="screen">
    /* Full-page specific styles */
     .thermometer {
        margin-left:0;
    }
    </style>
</head>
<body>
<?php
// POSSIBLE VALUES: win / lose
$config = array(
    'goal' => 'win',
    'goalplural' => 'wins',
    'teamname' => 'Rockies',
    'quote' => '',
    'quoted' => '',
    'credits' => '
            <em><a href="http://blogs.denverpost.com/rockies/recordtracker-dick_monfort/" target="_top">Inspired by Dick Monfort</a></em>',
);
?>
<style type="text/css" media="screen">
/* Blogs template override */
#wrapper { background-color: transparent; }
body { padding-left:4px; }

/* Thermometer column and text. Source: http://jsfiddle.net/gBW3Y/252/ */
 .thermometer {
    width:22px;
    height:150px;
    display:block;
    font:bold 14px/152px helvetica, arial, sans-serif;
    text-indent: 36px;
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
    width:230px;
    left:50px;
    position:absolute;
    display:block;
}
 p#credits {
    padding-top:210px;
    padding-left:0;
    line-height:1.2em;
    font-size:13px;
}
#record, .thermo_rate, .thermo_seasons, #credits { font-weight: normal; }
#thermo_quote { display:none; }
.widget_item .categorytopper { display:none;}

/* Sidebar-specific display styles */
#sidebar2 #thermo_quote { display:block; }
#sidebar2 .widget_item .categorytopper { display:block;}
#sidebar2 .widget_item { 
    height:380px;
}

/* Loss-Mode Styles */
.thermo_seasons, #thermo_quote { display:none!important; }
</style>
<?php
function get_stats($path)
{
    // Return an arrary of the statistics from the json file specified.
    $json = file_get_contents($path);
    $json_object = json_decode($json, true);
    foreach ( $json_object['stat'] as $item ):
        $stats[$item['@attributes']['type']] = $item['@attributes']['num'];
    endforeach;
    return $stats;
}

// Get the rest of the stats
$stats = array(
    'season' => 162,
    'wins_goal' => 81,
    'goal' => 81);
$stats = array_merge($stats, get_stats('output/season.json'));

$stats['games_played'] = $stats['games_won'] + $stats['games_lost'];
$stats['games_left'] = $stats['season'] - $stats['games_played'];
$stats['games_to_win'] = $stats['wins_goal'] - $stats['games_won'];
$stats['games_to_lose'] = $stats['goal'] - $stats['games_lost'];
$stats['games_to_goal'] = $stats['games_to_win'];
$stats['win_rate'] = $stats['games_won'] / $stats['games_played'];
$stats['loss_rate'] = 1 - $stats['win_rate'];
$stats['percent_won'] = $stats['games_won'] / $stats['wins_goal'];
$stats['percent_lost'] = $stats['games_lost'] / $stats['goal'];
$stats['percent'] = 100 - ($stats['percent_won'] * 100);
$stats['projected_wins'] = round($stats['win_rate'] * $stats['games_left']) + $stats['games_won'];
$stats['projected_losses'] = round($stats['loss_rate'] * $stats['games_left']) + $stats['games_lost'];
$stats['projected'] = $stats['projected_wins'];
$stats['projected_seasons'] = round(( $stats['wins_goal'] * ( 1 / $stats['win_rate'] ) ) / $stats['season'], 2);

// Get the numbers on the last ten games
$last_ten = get_stats('output/last_ten.json');
$last_ten['win_rate'] = $last_ten['games_won'] / 10;
$last_ten['projected'] = round($last_ten['win_rate'] * $stats['games_left']) + $stats['games_won'];

if ( trim($stats['games_lost']) == '' ) $stats['games_lost'] = 0;

if ( $config['goal'] == 'lose' )
{
    $stats['games_to_goal'] = $stats['games_to_lose'];
    $stats['percent'] = 100 - ($stats['percent_lost'] * 100);
    $stats['projected'] = $stats['projected_losses'];
    $stats['projected_seasons'] = round(( $stats['goal'] * ( 1 / $stats['loss_rate'] ) ) / $stats['season'], 2);
}
/*
// EXISTING DATA PULLED IN VIA season.json
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
<style type="text/css">
 .thermometer {
    background: -webkit-linear-gradient(top, #fff 0%, #fff <?php echo $stats['percent']; ?>%, #db3f02 <?php echo $stats['percent']; ?>%, #db3f02 100%);
}
</style>
<div class="widget_item">
    <div class="categorytopper"><a href="/rockies/recordtracker/"><?php echo $config['teamname']; ?> Record Tracker</a></div>
    <p id="thermo_quote">
        <span id="the_quote"><?php echo $config['quote']; ?></span> <span>&mdash; <span id="the_quoted"><?php echo $config['quoted']; ?></span></span>
    </p>
<span class="thermometer">
    <span class="thermo_label" id="thermo-text">
        <span id="headline">The <?php echo $config['teamname']; ?> need <?php echo $stats['games_to_goal']; ?> more <?php echo $config['goalplural']; ?> to reach .500 for the season.</span><br>
        <span id="record">
        Current record: <span id="wins"><?php echo $stats['games_won']; ?></span> wins, <span id="losses"><?php echo $stats['games_lost']; ?></span> losses.<br><br>
        </span>

        <span class="thermo_rate">At the rate the <?php echo $config['teamname']; ?> have won this season, they are projected to <?php echo $config['goal']; ?> <span id="rate"><?php echo $stats['projected']; ?></span> games.
            Based on the win-rate of the <?php echo $config['teamname']; ?>' previous ten games, they will win <?php echo $last_ten['projected']; ?>.
            <?php echo $stats['games_left']; ?> games remain.</span>
        <span class="thermo_seasons">and it will take <span id="seasons"><?php echo $stats['projected_seasons']; ?> seasons</span> to win <?php echo $stats['wins_goal']; ?>.</span><br>
    </span>
</span>
        <p id="credits">
<?php echo $config['credits']; ?>
        </p>
</div>
</body>
</html>
<?php
$markup = ob_get_flush();
file_put_contents('output/index.html', $markup);
?>
