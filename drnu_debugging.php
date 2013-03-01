<!DOCTYPE html> 
<html> 
<head> 
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/> 
	<title>DR NU</title> 
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
		font-size: 1.2em;
		padding: 20px 5px 5px 5px;
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
	.text_line {
		clear:both;
		margin-bottom:2px;
	}
	.w { 
		width: 320px;
		height: 380px;
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
	print '<a class="menu" href="?slug=videos/newest&links='.$links.'">Nyt på DR NU</a>';
	print '<a class="menu" href="?slug=videos/mostviewed&links='.$links.'">Mest sete</a>';
	print '<a class="menu" href="?slug=videos/lastchance&links='.$links.'">Udløber snart</a>';
	print '<a class="menu" href="?slug=programseries&links='.$links.'">Alle programmer</a>';
	if ($links=="on") {
		print '<a class=menu href="'.$_SERVER['PHP_SELF'].'?slug='.$slug.'&links=off">Hide direct title links</a>';
	} else {
		print '<a class=menu href="'.$_SERVER['PHP_SELF'].'?slug='.$slug.'&links=on">Show direct title links</a>';
	}
	print "\n<p class='text_line'>&nbsp;</p>\n";
	return ;
}

function showseries($slug, $links) {
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/programseries"), true);    
	$lng = count($JsonContent);     
	$letters=preg_split('/(?<!^)(?!$)/u', "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZÆØÅ"); # Multi-byte safe splitting taking cvare of æøå
	for ($i=0; $i < count($letters)-1; $i++) {
		$letter=$letters[$i];
		echo "<a href='#".$letters[$i]."'>".$letters[$i]."</a>";
		echo " - ";
	}
	echo "<a href='#".$letters[count($letters)-1]."'>".$letters[count($letters)-1]."</a><p>";
	echo "<table id='all_programs'>";
	echo "<tr><th>Antal&nbsp;</th><th>Serie</th><th>Sidst tilføjet</th><th>Labels</th></tr>\n";
	$oldletter="";
	for($i=0; $i<$lng; $i++){             
		$Slug="programseries/".$JsonContent[$i]["slug"]."/videos";      
		$videolink = "?id=".$JsonContent[$i]["newestVideoId"]."&links=".$links;
		if ($JsonContent[$i]["videoCount"] > 1) { 
			$videolink = "?slug=".$Slug."&links=".$links;
		}
		$newletter = preg_split('/(?<!^)(?!$)/u', $JsonContent[$i]["title"]);
		$newletter = $newletter[0]; # first character - multi-byte safe (UTF-8)
		echo "<tr>";
				echo "<td align=left>";
		echo $JsonContent[$i]["videoCount"];
		echo "</td>";
		echo "<td>";
		if ($newletter !== $oldletter) {
			echo "\n<a name='$newletter'>\n";
			$oldletter = $newletter;
		}
		echo '<a href="'.$videolink.'">'.$JsonContent[$i]["title"].'</a>';
		echo "</td>";
				echo "<td>";
		#echo $JsonContent[$i]["newestVideoPublishTime"];
		$video_date = explode("T",$JsonContent[$i]["newestVideoPublishTime"] );
		echo $video_date[0];
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
	$JsonContent = json_decode(file_get_contents("http://www.dr.dk/nu/api/".$slug), true); 
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
		echo "<br>";
		echo "Sendt: ".$JsonContent[$i]["formattedBroadcastTime"];
		echo "<br>";
		echo "Udløber: ".$JsonContent[$i]["formattedExpireTime"]; 
		echo "</p>";
		echo '<p>';
		echo '<a href="?id='.$id.'">Se udsendelsen</a>';
		$videolink = "?slug=programseries/".$JsonContent[$i]["programSerieSlug"]."/videos";      
		echo "<br>";
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
	# New column
	echo "<div class='single_video_container'>";
	echo "<img width=$width height=$height src=\"$url/$thumbnail\" alt='' />";
	print "<p class='text_line'></p>\n";
	if ($_GET["debug"]) {
		# Mp4 links
		echo "<P>Debug: mp4 links</p>";
		echo '<p><a href="'.$videoManifestUrl.'">videoManifestUrl: '.$videoManifestUrl.'</a></p>';
		$SubJsonContent = json_decode(file_get_contents($JsonContent["videoResourceUrl"]), true); 
		$suburls = $SubJsonContent["links"];
		# RTMP link
		$pos = strpos($videoManifestUrl, "mp4:CMS/Resources/");     
		$LinkCut = substr($videoManifestUrl, $pos);     
		$rtmp = 'rtmp://vod.dr.dk/'.$LinkCut;
		echo "<p><a href='".$rtmp."'>$rtmp</a> XBMC recoding of rtmp stream (?)</p>";
		for($i=0; $i<count($suburls); $i++){ 
			$SubUrl2 = $suburls[$i]["uri"];  
			$pos = strpos($SubUrl2, "CMS/Resources/");     
			$LinkCut = substr($SubUrl2, $pos);     
			$mp4 = 'http://vodfiles.dr.dk/'.$LinkCut;
			echo "<p><a href='".$mp4."'>Play video (mp4 bitrate: ".$suburls[$i]["bitrateKbps"].")</a></p>";
		}
	}
	#
	# Video links
	#
	# mp4 link - best quality
	# rtmp://vod.dr.dk/cms/mp4:CMS/Resources/dr.dk/NETTV/DR1/2012/10/02926da5-cd7e-4f2e-bd15-e71663cc8978/dae406f2abd84233a1e4279f5c762528_1428.mp4?ID=1280881
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
	#
	# Information about video
	#
	$Slug="programseries/".$JsonContent['programSerieSlug']."/videos";      
	echo "<p>";
	echo $JsonContent["title"];
	echo ' <a href="?slug='.$Slug.'">[ Se alle i serien]</a>';
	echo "</p>";
	echo "<p>".$JsonContent["description"]."</p>"; 
	echo "<p>Varighed ".$JsonContent["duration"]."</p>";             
	echo "<p>Sendt ".$JsonContent["formattedBroadcastTimeForTVSchedule"]." klokken ".$JsonContent["formattedBroadcastHourForTVSchedule"]."</p>"; 
	echo "<p>Udløber ".$JsonContent["formattedExpireTime"]."</p>"; 
	echo "</div>\n";
	# Links to videos
	if ($_GET["debug"]) {
		echo "<table>";
		echo "<tr><td colspan=2>";
		echo '<p><a href="'.$JsonContent["videoManifestUrl"].'">videoManifestUrl: '.$JsonContent["videoManifestUrl"].'</a> Will write rtmp link</p>';
		echo '<p><a href="'.$VideoManifestUrlCut.'">VideoManifestUrlCut: '.$VideoManifestUrlCut.'</a> Will open flash player</p>';
		echo '<p><a href="'.$JsonContent["videoResourceUrl"].'">videoResourceUrl: '.$JsonContent["videoResourceUrl"].'</a> Used for getting streaming links --> mp4 links</p>';
		$suburls = $SubJsonContent["links"];
		for($i=0; $i<count($suburls); $i++){ 
			$SubUrl2 = $suburls[$i]["uri"];  
			$pos = strpos($SubUrl2, "CMS/Resources/");     
			$LinkCut = substr($SubUrl2, $pos);     
			$mp4 = 'http://vodfiles.dr.dk/'.$LinkCut;
			echo "<p><a href='".$mp4."'>MP4 link $i: ".$mp4."</a> Bitrate: ".$suburls[$i]["bitrateKbps"]."</p>";
		}
		for($i=0; $i<count($suburls); $i++){ 
			echo '<h1>Content of $SubJsonContent["links"]['.$i.']</h1>';
			foreach (array_keys($suburls[$i]) as $key){
				echo "<p>";
				echo $key;
				echo $suburls[$i][$key];
				echo "</p>";
			}
		}
		echo "</td></tr>";
		echo '<tr><td><h1>Loop through $JsonContent</td></tr></h1>';
		foreach (array_keys($JsonContent) as $key){
			echo "<tr>";
			echo "<td>".$key."</td>";
			echo "<td>".$JsonContent[$key]."</td>";
			echo "</tr>\n";
		}
		echo "</table>";
	}
return;
}

# Actual start of web page
$links=$_GET["links"];
if ($links != "on") $links="off";

$slug=$_GET["slug"];
if (!$slug) $slug="videos/newest";

$id=$_GET["id"];

if ($_GET["debug"]) {
print "Links:".$links."\n";
print "Slug:".$slug."\n";
print "Id:".$id."\n";
print "phpself:".$_SERVER['PHP_SELF']."\n";
}

showmenu($slug, $links);

if ($id) {
	show_single_video($id, $links);
} elseif ($slug=="programseries") {
	showseries($slug, $links);
} else {
	showvideos($slug, $links);
}

?>  
</body> 
</html>

