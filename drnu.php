<!DOCTYPE html> 
<html> 
<head> 
<meta http-equiv="content-type" content="text/html; charset=utf-8"/> 
<META name="author" content="Palle Villesen">
<META name="keywords" content="Danmarks Radio, TV, DR NU, dr.dk/tv, drs udsendelser, mp4">
<META name="description" content="DR4tablets er et simpelt php script som læser DRs api - alt indhold ligger hos DR og streames direkte fra DR.">
<title>DR4tablets - simpelt interface til DRs udsendelser (som på www.dr.dk/tv)</title> 
<style type="text/css">     
body {
	font-family: Arial, Helvetica, Sanserif;
	font-size: 1em;
	text-decoration: none; 
	margin: 0px 20px 10px 20px;
	padding:0;
	background-color: #000;
	color:#FFF;
}
a {
color: #FFF;
}

input { background-color: #444;
	border-collapse:collapse;
	border:1px solid #000;
	color: #EEE;
}

img {
	text-align: top-left; 
	float: left; 
	padding: 0px 10px 10px 0px;
}
.menu {
	float: left; 
	margin: 0px 10px 0px 10px;
	background:#555;
	color: #EEE;
	font-size: 1em;
	font-weight: bold;
	padding: 50px 5px 5px 5px;
	text-decoration: none;
}
.menu2 {
	float: right; 
	margin: 0px 10px 0px 10px;
	background:#555;
	color: #EEE;
	font-size: 1em;
	font-weight: bold;
	padding: 50px 5px 5px 5px;
	text-decoration: none;
}
#all_programs  {
	border-collapse:collapse;
}
#all_programs td ,#all_programs th {
	border-left:0px;
	border-right:0px;
	border-top: 1px solid #fff;
	border-bottom: 1px solid #aaa;
	padding:5px 7px 5px 7px;
}
#all_programs th {
	font-size:1.1em;
	text-align:left;
	padding-top:5px;
	padding-bottom:4px;
	background-color:#eee;
	color:#111;
}

#all_programs th a{
	color:#000;
}

.text_line {
	clear:both;
	margin-bottom:2px;
}
.w { 
	width: 320px;
	height: 420px;
	float: left; 
	background:#333;
	margin: 10px 10px 10px 10px;
	padding: 10px 10px 10px 10px;
}
.single_video_container {
	width: 640px;
	float: left; 
	background:#333;
	margin: 10px 10px 10px 10px;
	padding: 10px 10px 10px 10px;
}
</style> 
</head> 
<body>
<?php 
function showmenu($slug, $links) {
	print '<a class="menu" href="?slug=videos/premiere&links='.$links.'">Premiere</a>';
	print '<a class="menu" href="?slug=videos/newest&links='.$links.'">Nyeste</a>';
	print '<a class="menu" href="?slug=videos/mostviewed&links='.$links.'">Mest sete</a>';
	print '<a class="menu" href="?slug=videos/lastchance&links='.$links.'">Udløber</a>';
	print '<a class="menu" href="?slug=programseries&links='.$links.'">Oversigt</a>';
	print '<form class="menu" method=POST action="?slug=search&links='.$links.'">';
	print '<INPUT type=text name=q size=10 maxlength=255 value="'.$_POST["q"].'">';
	print '<input type="submit" value="Søg">';
	print '</form>';
	if ($links=="on") {
		print '<a class=menu2 href="'.$_SERVER['PHP_SELF'].'?slug='.$slug.'&links=off">Hide title links</a>';
	} else {
		print '<a class=menu2 href="'.$_SERVER['PHP_SELF'].'?slug='.$slug.'&links=on">Show title links</a>';
	}
	print "\n<p class='text_line'>&nbsp;</p>\n";
	return ;
}
function showseries($slug, $links) {
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/programseries"), true);
	if ($_GET['sort'] =="antal") {
		usort($JsonContent, function($a, $b) { return $b['videoCount'] - $a['videoCount']; });
	} 
	if ($_GET['sort'] =="date") {
		usort($JsonContent, function($a, $b) { return strnatcmp($a['newestVideoPublishTime'], $b['newestVideoPublishTime']);  });
		$JsonContent=array_reverse($JsonContent);
	}
	if ($_GET['sort'] =="labels") {
		usort($JsonContent, function($a, $b) { return strnatcmp($a['labels'][0], $b['labels'][0]);  });
	}
	$lng = count($JsonContent);     
	if ($_GET['sort'] =="title" or !$_GET['sort']) {
	$letters=preg_split('/(?<!^)(?!$)/u', "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZÆØÅ"); # Multi-byte safe splitting taking cvare of æøå
	for ($i=0; $i < count($letters)-1; $i++) {
		$letter=$letters[$i];
		echo "<a href='#".$letters[$i]."'>".$letters[$i]."</a>";
		echo " - ";
	}
	echo "<a href='#".$letters[count($letters)-1]."'>".$letters[count($letters)-1]."</a><p>";
	}
	echo "<table id='all_programs'>";
	echo "<tr>";
	$videolink = '?slug=programseries&links='.$links.'&sort=antal';
	echo "<th><a href=$videolink>Antal&nbsp;</a></th>";
	$videolink = '?slug=programseries&links='.$links.'&sort=title';
	echo "<th><a href=$videolink>Serie</a></th>";
	$videolink = '?slug=programseries&links='.$links.'&sort=date';
	echo "<th><a href=$videolink>Sidst tilføjet</a></th>";
	$videolink = '?slug=programseries&links='.$links.'&sort=labels';
	echo "<th><a href=$videolink>Labels</a></th>";
	echo "</tr>\n";
	$oldletter="";
	$j=0;
	for($i=0; $i<$lng; $i++){             
		$Slug="programseries/".$JsonContent[$i]["slug"]."/videos";      
		$videolink = "?id=".$JsonContent[$i]["newestVideoId"]."&links=".$links;
		if ($JsonContent[$i]["videoCount"] > 1) { 
			$videolink = "?slug=".$Slug."&links=".$links;
		}
		$newletter = preg_split('/(?<!^)(?!$)/u', $JsonContent[$i]["title"]);
		$newletter = strtoupper($newletter[0]); # first character - multi-byte safe (UTF-8)
		echo "<tr>";
		echo "<td align=left>";
		echo $JsonContent[$i]["videoCount"];
		echo "</td>";
		echo "<td>";
		while ($newletter != $oldletter & $j < count($letters) ) {
			$oldletter=$letters[$j];
			echo "\n<a name='$oldletter'>\n";
			$j = $j +1;
		}
		echo '<a href="'.$videolink.'">'.$JsonContent[$i]["title"].'</a>';
		echo "</td>";
		echo "<td>";
		$video_date = explode("T",$JsonContent[$i]["newestVideoPublishTime"] );
		echo $video_date[0]." ".$video_date[1];
		echo "</td><td>";
		echo implode(",", $JsonContent[$i]["labels"]);
		echo "</td>";
		echo "</tr>";
		echo "\n";
	} 
	echo "</table>";
	echo "\n";
	return;
}
function showvideos($slug="videos/newest", $links) {
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/".$slug."?limit=100"), true); 
	$lng = count($JsonContent); 
	for($i=0; $i<$lng; $i++){ 
		$id=$JsonContent[$i]["id"]; 
		echo "<div class='w'>";
		$width=320; 
		$height=180;
		$thumbnail = $width."x".$height.".jpg"; 
		$url = 'http://www.dr.dk/nu/api/videos/'.$id.'/images' ;
		echo "<a href='?id=".$id."'><img width=$width height=$height src=\"$url/$thumbnail\" alt='' /></a>"; 
		print "<p class='text_line'></p>\n";
		echo "<p>";
		if ($links=="on") {
			echo "<strong><a href = ". get_mp4link_by_id($id) .">" .$JsonContent[$i]["title"]."</a></strong>";
		} else {
			echo "<strong>".$JsonContent[$i]["title"]."</strong>";
		}
		echo "</p><p>";
		echo "Sendt: ".$JsonContent[$i]["formattedBroadcastTime"]." ".$JsonContent[$i]["formattedBroadcastHourForTVSchedule"]."<br>"; 
		echo "Kanal: ".$JsonContent[$i]["broadcastChannel"]."<br>"; 
		echo "Udløber: ".$JsonContent[$i]["formattedExpireTime"]; 
		echo "</p><p>";
		echo '<a href="?id='.$id.'">Se udsendelsen</a></p><p>';
		$videolink = "?slug=programseries/".$JsonContent[$i]["programSerieSlug"]."/videos";
		echo '<a href="'.$videolink.'">Se alle i serien</a>';
		echo "</p>";
		echo "</div>\n";
	}
	return;
}
function get_mp4link_by_id($id) {
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/videos/".$id), true); 
	$videoManifestUrl = file_get_contents($JsonContent["videoManifestUrl"]);
	$pos = strpos($videoManifestUrl, "CMS/Resources/");     
	$LinkCut = substr($videoManifestUrl, $pos);     
	$mp4 = 'http://vodfiles.dr.dk/'.$LinkCut;
	if (strpos($mp4,'NETTV')) {
		return $mp4;
	}
}
function show_single_video($id, $links) {
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/videos/".$id), true); 
	$videoManifestUrl = file_get_contents($JsonContent["videoManifestUrl"]); # Get the rtmplinks
	$width=640;
	$height=360;
	$thumbnail = $width."x".$height.".jpg"; 
	$url = 'http://www.dr.dk/nu/api/videos/'.$id.'/images' ;
	echo "<div class='single_video_container'>";
	echo "<img width=$width height=$height src=\"$url/$thumbnail\" alt='' />";
	print "<p class='text_line'></p>\n";
	# Video links
	$pos = strpos($videoManifestUrl, "CMS/Resources/");     
	$LinkCut = substr($videoManifestUrl, $pos);     
	$mp4 = 'http://vodfiles.dr.dk/'.$LinkCut;
	echo "<h1>";
	echo "Afspil: <a href='".$mp4."'>Mp4</a>";
	echo " - ";
	# RTMP links
	echo '<a href="'.$videoManifestUrl.'">RTMP</a>';
	echo " - ";
	# Flash links
	$pos = strpos($JsonContent["videoManifestUrl"], "&"); 
	$VideoManifestUrlCut = substr($JsonContent["videoManifestUrl"], 0, $pos);
	echo '<a href="'.$VideoManifestUrlCut.'">FLASH</a>';
	echo "</h1>";
	# Information about video
	$Slug="programseries/".$JsonContent['programSerieSlug']."/videos";      
	echo "<p>";
	echo $JsonContent["title"];
	echo ' <a href="?slug='.$Slug.'">[ Se alle i serien]</a>';
	echo "</p>";
	echo "<p>".$JsonContent["description"]."</p>"; 
	echo "<p>Varighed ".$JsonContent["duration"]."</p>";             
	echo "<p>Sendt ".$JsonContent["formattedBroadcastTime"]." klokken ".$JsonContent["formattedBroadcastHourForTVSchedule"]."</p>"; 
	echo "<p>Kanal: ".$JsonContent["broadcastChannel"]."</p>"; 
	echo "<p>Udløber ".$JsonContent["formattedExpireTime"]."</p>"; 
	echo "</div>\n";
return;
}
# Actual start of web page
$links=$_GET["links"];
$id=$_GET["id"];
$slug=$_GET["slug"];
if ($links != "on") $links="off";
if (!$slug) $slug="videos/mostviewed"; # If no slug defined, default to most viewed
if ($slug=="search" & !$_POST["q"]) $slug="programseries"; # Empty search takes you to the big table instead
if ($slug=="search") $slug=urlencode("search/".$_POST["q"]); 
# Now create the page
showmenu($slug, $links);
if ($id) show_single_video($id, $links);
elseif ($slug=="programseries") showseries($slug, $links);
else  showvideos($slug, $links);
?>  
</body> 
</html>
