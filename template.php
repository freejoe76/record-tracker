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
    <script src="http://crime.denverpost.com/static/js/d3.v3.min.js" charset="utf-8"></script>

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
#thermo { 
    float: left;
}
/* Blogs template override */
#wrapper { background-color: transparent; }
body {
    padding-left:4px;
    font:bold 14px/152px helvetica, arial, sans-serif;
}
 .thermometer {
    width:22px;
    height:150px;
    display:block;
    font:bold 14px/152px helvetica, arial, sans-serif;
    text-indent: 36px;
    color:#4a1c03;
}
 .thermo_label {
    text-indent:0;
    font:bold 14px/20px helvetica, arial, sans-serif;
    width:230px;
    left:100px;
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
/*
 .thermometer {
    background: -webkit-linear-gradient(top, #fff 0%, #fff <?php echo $stats['percent']; ?>%, #db3f02 <?php echo $stats['percent']; ?>%, #db3f02 100%);
}
*/
</style>
<div class="widget_item">
    <div class="categorytopper"><a href="/rockies/recordtracker/"><?php echo $config['teamname']; ?> Record Tracker</a></div>
    <p id="thermo_quote">
        <span id="the_quote"><?php echo $config['quote']; ?></span> <span>&mdash; <span id="the_quoted"><?php echo $config['quoted']; ?></span></span>
    </p>

    <div id="thermo"></div>
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
<script>
var width = 80,
    height = 180,
    maxTemp = 20.2,
    minTemp = 15.4,
    currentTemp = 19.2;

var bottomY = height - 5,
    topY = 5,
    bulbRadius = 20,
    tubeWidth = 21.5,
    tubeBorderWidth = 1,
    mercuryColor = "rgb(230,0,0)",
    innerBulbColor = "rgb(230, 200, 200)"
    tubeBorderColor = "#999999";

var bulb_cy = bottomY - bulbRadius,
    bulb_cx = width/2,
    top_cy = topY + tubeWidth/2;


var svg = d3.select("#thermo")
  .append("svg")
  .attr("width", width)
  .attr("height", height);


var defs = svg.append("defs");

// Define the radial gradient for the bulb fill colour
var bulbGradient = defs.append("radialGradient")
  .attr("id", "bulbGradient")
  .attr("cx", "50%")
  .attr("cy", "50%")
  .attr("r", "50%")
  .attr("fx", "50%")
  .attr("fy", "50%");

bulbGradient.append("stop")
  .attr("offset", "0%")
  .style("stop-color", innerBulbColor);

bulbGradient.append("stop")
  .attr("offset", "90%")
  .style("stop-color", mercuryColor);




// Circle element for rounded tube top
svg.append("circle")
  .attr("r", tubeWidth/2)
  .attr("cx", width/2)
  .attr("cy", top_cy)
  .style("fill", "#FFFFFF")
  .style("stroke", tubeBorderColor)
  .style("stroke-width", tubeBorderWidth + "px");


// Rect element for tube
svg.append("rect")
  .attr("x", width/2 - tubeWidth/2)
  .attr("y", top_cy)
  .attr("height", bulb_cy - top_cy)
  .attr("width", tubeWidth)
  .style("shape-rendering", "crispEdges")
  .style("fill", "#FFFFFF")
  .style("stroke", tubeBorderColor)
  .style("stroke-width", tubeBorderWidth + "px");


// White fill for rounded tube top circle element
// to hide the border at the top of the tube rect element
svg.append("circle")
  .attr("r", tubeWidth/2 - tubeBorderWidth/2)
  .attr("cx", width/2)
  .attr("cy", top_cy)
  .style("fill", "#FFFFFF")
  .style("stroke", "none")



// Main bulb of thermometer (empty), white fill
svg.append("circle")
  .attr("r", bulbRadius)
  .attr("cx", bulb_cx)
  .attr("cy", bulb_cy)
  .style("fill", "#FFFFFF")
  .style("stroke", tubeBorderColor)
  .style("stroke-width", tubeBorderWidth + "px");


// Rect element for tube fill colour
svg.append("rect")
  .attr("x", width/2 - (tubeWidth - tubeBorderWidth)/2)
  .attr("y", top_cy)
  .attr("height", bulb_cy - top_cy)
  .attr("width", tubeWidth - tubeBorderWidth)
  .style("shape-rendering", "crispEdges")
  .style("fill", "#FFFFFF")
  .style("stroke", "none");


// Scale step size
var step = 5;

// Determine a suitable range of the temperature scale
var domain = [
  step * Math.floor(minTemp / step),
  step * Math.ceil(maxTemp / step)
  ];

if (minTemp - domain[0] < 0.66 * step)
  domain[0] -= step;

if (domain[1] - maxTemp < 0.66 * step)
  domain[1] += step;


// D3 scale object
var scale = d3.scale.linear()
  .range([bulb_cy - bulbRadius/2 - 8.5, top_cy])
  .domain(domain);


// Max and min temperature lines
[minTemp, maxTemp].forEach(function(t) {

  var isMax = (t == maxTemp),
      label = (isMax ? "max" : "min"),
      textCol = (isMax ? "rgb(230, 0, 0)" : "rgb(0, 0, 230)"),
      textOffset = (isMax ? -4 : 4);

  svg.append("line")
    .attr("id", label + "Line")
    .attr("x1", width/2 - tubeWidth/2)
    .attr("x2", width/2 + tubeWidth/2 + 22)
    .attr("y1", scale(t))
    .attr("y2", scale(t))
    .style("stroke", tubeBorderColor)
    .style("stroke-width", "1px")
    .style("shape-rendering", "crispEdges");

  svg.append("text")
    .attr("x", width/2 + tubeWidth/2 + 2)
    .attr("y", scale(t) + textOffset)
    .attr("dy", isMax ? null : "0.75em")
    .text(label)
    .style("fill", textCol)
    .style("font-size", "11px")

});


var tubeFill_bottom = bulb_cy,
    tubeFill_top = scale(currentTemp);

// Rect element for the red mercury column
svg.append("rect")
  .attr("x", width/2 - (tubeWidth - 10)/2)
  .attr("y", tubeFill_top)
  .attr("width", tubeWidth - 10)
  .attr("height", tubeFill_bottom - tubeFill_top)
  .style("shape-rendering", "crispEdges")
  .style("fill", mercuryColor)


// Main thermometer bulb fill
svg.append("circle")
  .attr("r", bulbRadius - 6)
  .attr("cx", bulb_cx)
  .attr("cy", bulb_cy)
  .style("fill", "url(#bulbGradient)")
  .style("stroke", mercuryColor)
  .style("stroke-width", "2px");


// Values to use along the scale ticks up the thermometer
var tickValues = d3.range((domain[1] - domain[0])/step + 1).map(function(v) { return domain[0] + v * step; });


// D3 axis object for the temperature scale
var axis = d3.svg.axis()
  .scale(scale)
  .innerTickSize(7)
  .outerTickSize(0)
  .tickValues(tickValues)
  .orient("left");

// Add the axis to the image
var svgAxis = svg.append("g")
  .attr("id", "tempScale")
  .attr("transform", "translate(" + (width/2 - tubeWidth/2) + ",0)")
  .call(axis);

// Format text labels
svgAxis.selectAll(".tick text")
    .style("fill", "#777777")
    .style("font-size", "10px");

// Set main axis line to no stroke or fill
svgAxis.select("path")
  .style("stroke", "none")
  .style("fill", "none")

// Set the style of the ticks 
svgAxis.selectAll(".tick line")
  .style("stroke", tubeBorderColor)
  .style("shape-rendering", "crispEdges")
  .style("stroke-width", "1px");



</script>
</body>
</html>
<?php
$markup = ob_get_flush();
file_put_contents('output/index.html', $markup);
?>
