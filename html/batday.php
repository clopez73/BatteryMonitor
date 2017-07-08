<!DOCTYPE html>
<html>
<head>

<?php
//error handler function
function customError($errno, $errstr) {
  echo "<b>Error:</b> [$errno] $errstr";
}

//set error handler
set_error_handler("customError");
?>
<style>
#body {
    margin-top:0em;
    margin-bottom:0em;
    line-height:1.2em;
    background-color:black; color:grey;

}
#header {
    margin-top:0em;
    margin-bottom:0em;
    line-height:1em;

#    background-color:black;
#    color:white;
    text-align:center;
#    padding:5px;
    width:500px;
}
#bat {
    margin-top:0em;
    margin-bottom:0em;
    line-height:1.2em;
    font-size:1em;
    background-color:#eeeeee;
    height:300px;
    width:<?php echo $batwidth; ?>px;
    float:left;
    padding:5px;
    color:black;	      
}
#batdata {
    margin-top:-0.9em;
    margin-bottom:0em;
    width:220px;
    height:300px;
    float:left;
    padding:10px;
    font-size:3.0em;
    line-height:0.2em;
#    border: 1px solid white;	 	 
}
#footer {
    margin-top:0em;
    margin-bottom:0em;
#    background-color:black;
#    color:white;
    width:650px;
    float: left;
    clear:both;
#    text-align:center;
#   padding:5px;	 	 
}

#highest {
    font-weight: 900;
    font-size: 1.1em;
}

#middle {
    font-style: normal;
    font-size: 1.0em;
}
#lowest {
    font-style: oblique;
    font-weight: bold;
    font-size: 0.9em;
}

</style>

<?php
$config = file_get_contents("/home/simat/BatteryMonitor/battery.cfg");
$pos = strpos($config,"summary");
$pos = strpos($config,"'",$pos);
$summary = substr($config,$pos+1);
$summary = strstr($summary,"'",true);
$pos = strpos($config,"name");
$pos = strpos($config,"'",$pos);
$batname = substr($config,$pos+1);
$batname = strstr($batname,"'",true);
$summary = file_get_contents($summary);
# echo $config;
$pos = strpos($config,"capacity");
$batcapacity = substr($config,$pos);
$batcapacity = strstr($batcapacity,"\n",true);
sscanf($batcapacity, "capacity = %u",$batcapacity);
# echo $batcapacity;
$highv = 3.6;
$lowv = 3.0;
#echo $summary
$pos = strpos($summary,"[current]");

$pos = strpos($summary,"'",$pos);
$timestamp = substr($summary,$pos+1);
$timestamp = strstr($timestamp,"'",true);

function getdat($dataname) { 
   global $summary, $pos;
   $pos = strpos($summary, $dataname, $pos);
   $pos = strpos($summary,"[",$pos);
   $data = substr($summary,$pos+1);
   $data = strstr($data,"]",true);
   $data = explode(",",$data);
   return $data;
}

$batvolts = getdat("maxvoltages");
$deltav = getdat("deltav");
$amps = getdat("ioutmax");
$dod = getdat("dod");
$capacity = round(100*($batcapacity-$dod[0])/$batcapacity);
if  ($capacity > "75") {
   $capcolour="green";
}  elseif ($capacity > "50") {
      $capcolour="gold";
}  elseif ($capacity  > "25") {
      $capcolour="darkorange";
}  else {
      $capcolour="red";
}
$pos = strpos($summary,"[currentday]", $pos);
$daymaxv = getdat("maxvoltages");
$daymaxnochargev = getdat("maxnocharge");
$dayminnoloadv = getdat("minnoload");
$dayminloadv = getdat("minvoltages");
$dayah = getdat("ah");
$power = getdat("power");

$numbercells = count($batvolts)-1;
$batwidth = $numbercells*38;
$batcolour = array_fill(0,$numbercells,"yellow");

$minbatvolts = 100.0;
$dayminmin = 100.0 ;
$maxbatvolts = 0.0;
$daymaxmax = 0.0;
$dayminmax = 100.0 ;
$daymaxnocharge = 0.0;
$dayminnocharge = 100.0 ;
$daymaxmin = 0.0;
$dayminmin = 100.0 ;
for ($x = 0; $x < $numbercells; $x++) {
    if ($batvolts[$x] >= $highv) {
       $batcolour[$x] = "red"; }
    if ($batvolts[$x] <= $lowv) {
       $batcolour[$x] = "fuchsia"; }
    $minbatvolts = min($minbatvolts,$batvolts[$x]);
    $maxbatvolts = max($maxbatvolts,$batvolts[$x]);
    $daymaxmax = max($daymaxmax,$daymaxv[$x]);
    $dayminmax = min($dayminmax,$daymaxv[$x]);
    $daymaxnocharge = max($daymaxnocharge,$daymaxnochargev[$x]);
    $dayminnocharge = min($dayminnocharge,$daymaxnochargev[$x]);
    $dayminv[$x] = min($dayminnoloadv[$x],$dayminloadv[$x]);
    $daymaxmin = max($daymaxmin,$dayminv[$x]);
    $dayminmin = min($dayminmin,$dayminv[$x]);
}

$batvoltstype = array_fill(0,$numbercells+1,"middle");
$batmaxtype = array_fill(0,$numbercells+1,"middle");
$batnochargetype = array_fill(0,$numbercells+1,"middle");
$batmintype = array_fill(0,$numbercells+1,"middle");

for ($x = 0; $x < $numbercells; $x++) {

    if ($batvolts[$x] == $maxbatvolts) {
       $batvoltstype[$x] = "highest"; }
    if ($batvolts[$x] == $minbatvolts) {
       $batvoltstype[$x] = "lowest"; }

    if ($daymaxv[$x] == $daymaxmax) {
       $batmaxtype[$x] = "highest"; }
    if ($daymaxv[$x] == $dayminmax) {
       $batmaxtype[$x] = "lowest"; }

    if ($daymaxnochargev[$x] == $daymaxnocharge) {
       $batnochargetype[$x] = "highest"; }
    if ($daymaxnochargev[$x] == $dayminnocharge) {
       $batnochargetype[$x] = "lowest"; }

    if ($dayminv[$x] == $daymaxmin) {
       $batmintype[$x] = "highest"; }
    if ($dayminv[$x] == $dayminmin) {
       $batmintype[$x] = "lowest"; }
}

/*
$arrlength = count($batvoltstype);

for($x = 0; $x < $arrlength; $x++) {
    echo ($x.$batvoltstype[$x]);
}
*/


?>
</head>
<body id="body">
<!-- <meta http-equiv="refresh" content="60"> -->
<meta http-equiv=cache-control" content="no-cache">
<meta http-equiv=cache-control" content="no-cache">
<meta http-equiv=pragma" content="no-cache">
<div id="header">
<!-- <p style=font-size:24px; font-weight:900> Geoff's Battery Data Dated </p> -->
<!-- <h2> Karrak Battery Data </h2> -->

<?php
echo ("<h2>" . $batname." Battery Data </h2>");
echo "<p><b>" . (date("l jS \of F Y h:i:s A", strtotime($timestamp)) . "</b></p>"); ?>
</div>
<div id="bat">
<table align="left" border="1" cellpadding="1" cellspacing="4" style="height:300px; width:<?php echo ($numbercells*38); ?>px">
	<tbody>
		<tr>
<?php for ($x = ($numbercells/2); $x < $numbercells; $x++) { 

			echo ('<td style="background-color:' . $batcolour[$x] . ';text-align:center">');
			echo ('<p> <span style="font-size:2.0em">' . ($x+1) . '</span><br>');
			echo ('<span id= "' . $batmaxtype[$x] . '"> <span style="font-size:0.8em">' . $daymaxv[$x] . 'V </span></span><br>');
			echo ('<span id= "' . $batnochargetype[$x] . '"> <span style="font-size:0.8em">' . $daymaxnochargev[$x] . 'V </span></span><br>');
			echo ('<span id= "' . $batvoltstype[$x] . '">' . $batvolts[$x] .'V </span><br>');
			echo ('<span id= "' . $batmintype[$x] . '"> <span style="font-size:0.8em">' . $dayminv[$x] . 'V </span></span><br>');
			echo ('</p></td>');
} ?>
		</tr>
		<tr>
<?php for ($x = $numbercells/2-1; $x >= 0; $x--) { 

			echo ('<td style="background-color:' . $batcolour[$x] . ';text-align:center">');
			echo ('<p> <span style="font-size:2.0em">' . ($x+1) . '</span><br>');
			echo ('<span id= "' . $batmaxtype[$x] . '"> <span style="font-size:0.8em">' . $daymaxv[$x] . 'V </span></span><br>');
			echo ('<span id= "' . $batnochargetype[$x] . '"> <span style="font-size:0.8em">' . $daymaxnochargev[$x] . 'V </span></span><br>');
			echo ('<span id= "' . $batvoltstype[$x] . '">' . $batvolts[$x] .'V </span><br>');
			echo ('<span id= "' . $batmintype[$x] . '"> <span style="font-size:0.8em">' . $dayminv[$x] . 'V </span></span><br>');
			echo ('</p></td>');
} ?>
		</tr>
	</tbody>
</table>
</div>
<div id="batdata">
<p style="text-align:center;font-size:2.0em;line-height:0.0em;color:<?php echo $capcolour?>">
<?php echo $capacity."%"; ?></p>
<p style="font-size:1.5em;line-height:0.0em;color:LightGray"><?php echo $amps[0]."A"?></p>
<p><?php echo $dod[0]."Ah"?></p>
<p><?php echo $batvolts[$numbercells]."V"?></p>
<p><?php echo $deltav[0]."V"?></p>
</div>

<div id="footer">
<h2> Daily Battery Statistics </h2>
<!--</small><br>-->
<b>Daily Battery Charge In&nbsp;</b><?php echo $dayah[4]."Ah"?><br>
<b>Daily Battery Charge Out&nbsp;</b><?php echo $dayah[5]."Ah"?><br>
<b>Daily Battery Power In&nbsp;</b><?php echo $power[0]."kWh"?><br>
<b>Daily Battery Power Out&nbsp;</b><?php echo $power[1]."kWh"?><br>
<b>Daily Solar Power In&nbsp;</b><?php echo $power[2]."kWh"?><br>
<b>Daily Power Out&nbsp;</b><?php echo $power[3]."kWh"?></p>


</div>
</body>
</html> 
