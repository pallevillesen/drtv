<?php 
#
# This script relies on api code made by Tommy Winther
# http://tommy.winther.nu
# 
#  This Program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2, or (at your option)
#  any later version.
#
#  This Program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
#  GNU General Public License for more details.
#

class TvApi
{

public function bundlesWithPublicAsset( $letter=null, $limit=50, $offset=null, $bundleType='Series', $channelType='TV') {
	#http://www.dr.dk/mu/View/bundles-with-public-asset?limit=100&ChannelType='TV'&offset=$eq(2400)&Title=$orderby('asc')
	$params = 'ChannelType=$eq("'.$channelType.'")&limit=$eq('.$limit.')&Title=$orderby("asc")';
	if ($letter!=null) {
		$params= $params . '&Title=$like("'.$letter.'")';
	}
	if ($offset!=null) {
		$params= $params . '&offset=$eq('.$offset.')';
	}
	$url='http://www.dr.dk/mu/view/bundles-with-public-asset';
	return $this->_http_request($url, $params);
}

public function programCardRelations($relationsSlug, $limit=25, $offset=null) {
	$params = 'Relations.Slug=$eq("'.$relationsSlug.'")&limit='.$limit;
	if ($offset!=null) {
		$params= $params . '&offset=$eq('.$offset.')';
	}
	$url='http://www.dr.dk/mu/programcard';
	return $this->_http_request($url, $params);
}

public function searchProgramCards($limit=25, $offset=null, $field=null, $searchtext=null) {
	if (count(explode(" ",$searchtext)) > 1) {
		$params= $field.'="'.rawurlencode($searchtext).'"';
	} else {
		$params= $field.'=$like("'.$searchtext.'")';
	}
	$params = $params ."&PrimaryAssetKind='VideoResource'&limit=".$limit;
	if ($offset>0) {
		$params= $params . '&offset=$eq('.$offset.')';
	}
	$url='http://www.dr.dk/mu/search/programcard';
	if ($_GET["debug"]) {
		print "<pre>";
		echo "Raw ",var_dump($searchtext);
		echo "rawurlencode ", var_dump(rawurlencode($searchtext));
		echo "rawurldecode ", var_dump(rawurldecode($searchtext));
		echo "rawurldecode+urlencode ", var_dump(urlencode(rawurldecode($searchtext)));
		echo "urlencode ", var_dump(urlencode($searchtext));
		echo "urldecode ", var_dump(urldecode($searchtext));
		echo "strip ", var_dump(stripslashes($searchtext));
		echo $url."?".$params;
		print "</pre>";
	}
	return $this->_http_request($url, $params);
}

public function getMostViewedProgramCards($days) {
	$params = 'days=$eq("'.$days.'")';
	$params = $params . '&ChannelType=TV';
	$params = $params . '&count=50';
	$url='http://www.dr.dk/mu/View/programviews';
	if ($_GET["debug"]) {
		print "<pre>";
		echo $url."?".$params;
		print "</pre>";
	}
	return $this->_http_request($url, $params);
}

public function recentProgramCards($count, $offset=null) {
	$params = 'count='.$count;
	if ($offset!=null) {
		$params= $params . '&offset=$eq('.$offset.')';
	}
	$url='http://www.dr.dk/mu/ProgramViews/RecentViews';
	if ($_GET["debug"]) {
		print "<pre>";
		echo $url."?".$params;
		print "</pre>";
	}
	return $this->_http_request($url, $params);
}

public function programCard($slug) {
	$url='http://www.dr.dk/mu/programcard/expanded/';
	return $this->_http_request($url.$slug);
}


public function _http_request($url, $params=NULL) {
	if ($params!=NULL) {
		$url = $url.'?'.$params;
	}
	$content = file_get_contents($url);
	$decoded_content = json_decode($content, true);
	return $decoded_content;
}

public function getAsset($kind, $programCard) {
	if (array_key_exists('ProgramCard', $programCard)) {
		$programCard = $programCard['ProgramCard'];
	}
	if (array_key_exists('Assets', $programCard)) {
		foreach ($programCard['Assets'] as $asset) {
			if ($asset['Kind'] == $kind) {
				return $asset;
			}
		}
	}
return NULL;
}


public function getMultipleAssets($kind, $programCard) {
	if (array_key_exists('ProgramCard', $programCard)) {
		$programCard = $programCard['ProgramCard'];
	}
	if (array_key_exists('Assets', $programCard)) {
		$assets=array();
		foreach ($programCard['Assets'] as $asset) {
			if ($asset['Kind'] == $kind) {
				$assets[]= $asset;
			}
		}
		return $assets;
	}
return NULL;
}



public function getRelation($kind, $programCard) {
	if (array_key_exists('ProgramCard', $programCard)) {
		$programCard = $programCard['ProgramCard'];
	}
	if (array_key_exists('Relations', $programCard)) {
		foreach ($programCard['Relations'] as $Relation) {
			if ($Relation['BundleType'] == $kind) {
				return $Relation;
			}
		}
	}
return NULL;
}

public function getMultipleRelations($kind, $programCard) {
	if (array_key_exists('Relations', $programCard)) {
		$relations=array();
		foreach ($programCard['Relations'] as $relation) {
			if ($relation['Kind'] == $kind) {
				$relations[]= $relation;
			}
		}
		return $relations;
	}
return NULL;
}



public function getLink($asset, $target = null) {
	#Loop through list of video links, and get the one with highest bitrate from a specific asset (typical "android")
	$bitRate = 0;
	$uri = null;
	if (array_key_exists('Links', $asset)) {
		foreach ($asset['Links'] as $link) {
			if ( ($target==null or $link['Target'] == $target) and (array_key_exists('Bitrate', $link) and $link['Bitrate'] > $bitRate) )  {
				$uri = $link['Uri'];
				$bitRate = $link['Bitrate'];
			} elseif (!array_key_exists('Bitrate', $link) and $uri==null) {
				$uri = $link['Uri'];
			}
		}
	}
	return $uri;
}

#
# End of Class definition
}

function showHeader() {
print '
<!DOCTYPE html> 
<html> 
<head> 
<meta http-equiv="content-type" content="text/html; charset=utf-8"/> 
<META name="author" content="Palle Villesen">
<META name="keywords" content="Danmarks Radio, TV, DR NU, dr.dk/tv, drs udsendelser, mp4">
<META name="description" content=" DRs programmer til tablets - simpelt interface til DRs udsendelser (som på www.dr.dk/tv). Alt indhold ligger hos DR og streames direkte fra DR.">
<title>DR | MP4</title> 
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
	min-height:60px;
	background:#555;
	color: #EEE;
	font-size: 1em;
	font-weight: bold;
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

#all_programs th a{
	color:#000;
}

.text_line {
	clear:both;
	margin-bottom:2px;
}
.w { 
	width: 320px;
	height: 530px;
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

';

}

function showMenu() {
	print '<a class="menu" href="?slug=hoejdepunkter">Højdepunkter</a>';
	print '<a class="menu" href="?slug=forpremierer">Forpremierer</a>';
	print '<a class="menu" href="?slug=test-spotliste">Spotlist</a>';
	print '<a class="menu" href="?slug=recent">Set nu!<br></a>';
	print '<a class="menu" href="?slug=mostviewed1">Seneste<br>døgn</a>';
	print '<a class="menu" href="?slug=mostviewed7">Seneste<br>uge</a>';
	print '<a class="menu" href="?slug=mostviewed31">Seneste<br>måned</a>';
	print '<a class="menu" href="?slug=bundles">Serier</a>';
	print '<form class="menu" method=GET action="">';
	print '<INPUT type=text name=searchtext size=10 maxlength=255 value="'.$_GET["searchtext"].'">';
	print '<input type="submit" value="Søg">';
	print '</form>';
	print "\n<p class='text_line'>&nbsp;</p>\n";
	return ;
}

// function parseDate($dateString) {
	// $pattern ='(\d+)\-(\d+)\-(\d+)T(\d+):(\d+):(\d+)';
	// $subject= $dateString;
	// preg_match($pattern, $subject, $matches);
	// $year = int($matches[1]);
	// $month = int($matches[2]);
	// $day = int($matches[3]);
	// $hours = int($matches[4]);
	// $minutes = int($matches[5]);
	// $seconds = int($matches[6]);
	// return date('l jS \of F Y h:i:s A', mktime($hours, $minutes, $seconds, $month, $day, $year));
// }

function  createInfoLabels($programCard) {
	$infoLabels = array();
	if ($programCard['Title']!=null) {
		$infoLabels['Title'] = $programCard['Title'];
	} else {
		$infoLabels['Title'] = "Ukendt titel";
	}
	if (array_key_exists("Description", $programCard) and $programCard['Description']!=null) {
		$infoLabels['Description'] = $programCard['Description'];
	}
	if (array_key_exists("Subtitle", $programCard) and $programCard['Subtitle']!=null) {
		$infoLabels['Subtitle'] = $programCard['Subtitle'];
	}
	if (array_key_exists('PrimaryBroadcastStartTime', $programCard)) {
		$infoLabels['sent'] = strtotime($programCard['PrimaryBroadcastStartTime']);
	}
	if (array_key_exists('PrimaryAssetEndPublish', $programCard)) {
		$infoLabels['expires'] = strtotime($programCard['PrimaryAssetEndPublish']);
	}
	if (array_key_exists('GenreText', $programCard)) {
		$infoLabels['GenreText'] = $programCard['GenreText'];
	}
	if (array_key_exists('OnlineGenreText', $programCard)) {
		$infoLabels['OnlineGenreText'] = $programCard['OnlineGenreText'];
	}
	return $infoLabels;
}

function listBundles($api,$bundles) {
	$params = $_GET;
	$offset=$_GET["offset"];
	$start=max(0, $offset-100);
	$params['offset'] = null;
	$paramString0 = http_build_query($params);
	$params['offset'] = $start;
	$paramString1 = http_build_query($params);
	$params['offset'] = $offset+100;
	$paramString2 = http_build_query($params);
	# Replace offset if url - link to this again
	echo "<a href='?".$paramString0."'>[<<]</a> - ";
	echo "<a href='?".$paramString1."'>[<]</a> - ";
	echo "<a href='?".$paramString2."'>[>]</a>";
	echo "Viser i øjeblikket: [".$offset."-".($offset+100)."] - ";
	echo "<a href='?slug=bundles'>Se Alle</a> ";
	echo "<p>";
	$letters=preg_split('/(?<!^)(?!$)/u', "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZÆØÅ"); # Multi-byte safe splitting taking care of æøå
	# Show letter navigation
	for ($i=0; $i < count($letters)-1; $i++) {
		$letter=$letters[$i];
		echo "<a href='?slug=bundles&letter=".$letters[$i]."'>_".$letters[$i]."_</a>";
		echo " - ";
	}
	echo "<a href='#".$letters[count($letters)-1]."'>_".$letters[count($letters)-1]."_</a><p>";
	echo "</p>";
	echo "<table id='all_programs'>";
	echo "<tr>";
	echo "<th>Serie</th>";
	echo "<th>Sidst tilføjet</th>";
	echo "<th>Channels</th>";
	echo "</tr>\n";
	$oldletter="";
	$j=0;
	$bundles = $bundles["Data"];
	usort($bundles, function($a, $b) { return strcmp($a['Title'], $b['Title']);  });
	foreach ($bundles as $bundle) {
		if (array_key_exists('ProgramCard', $bundle) and  $bundle['ProgramCard']['PrimaryAssetKind'] != 'VideoResource' ) {
			# Skipping all bundles without video!
			continue;
		}
		if (array_key_exists('ProgramCard', $bundle)) { 
			$programCard = $bundle['ProgramCard'];
		} else {
			$programCard = $bundle;
		}
		$newletter = preg_split('/(?<!^)(?!$)/u', $bundle["Title"]);
		$newletter = strtoupper($newletter[0]); # first character - multi-byte safe (UTF-8)
		echo "<tr>";
		echo "<td align=left>";
		if (in_array($newletter, $letters) and ($_GET['sort'] =="title" or !$_GET['sort'] )) {
			while ($newletter != $oldletter & $j < count($letters) ) {
				$oldletter=$letters[$j];
				echo "\n<a name='$oldletter'>\n";
				$j = $j +1;
			}
		}
		$infoLabels = createInfoLabels($programCard); # Format some information
		$url = $_SERVER['PHP_SELF']."?slug=". $bundle['Slug'];
		echo "<a href='".$url."'>".$bundle["Title"]."</a></p>";
		echo "</td>";
		echo "<td>";
		print date('j.n.Y \k\l. H:i',$infoLabels["sent"]);
		echo "</td><td>";
		echo "labels";
		echo "</td>";
		echo "</tr>";
		echo "\n";
	} 
	echo "</table>";
	echo "\n";
	return true;
}

function listVideos($api,$programCards, $navbar=false) {
	$limit=50;
	if (array_key_exists("Data", $programCards)) { 
		$programCards = $programCards["Data"];
	}
	# Shows not all videos - so make a navigation bar
	if ($navbar) {
		$params = $_GET;
		$offset=$_GET["offset"];
		$start=max(0, $offset-$limit);
		$params['offset'] = null;
		$paramString0 = http_build_query($params);
		$params['offset'] = $start;
		$paramString1 = http_build_query($params);
		$params['offset'] = $offset+$limit;
		$paramString2 = http_build_query($params);
		echo "<a href='?".$paramString0."'>[<<]</a> - ";
		echo "<a href='?".$paramString1."'>[<]</a> - ";
		echo "<a href='?".$paramString2."'>[>]</a> - ";
		echo "Viser i øjeblikket: [".$offset."-".($offset+$limit)."]";
		print "\n<p class='text_line'>&nbsp;</p>\n";
	}
	# Loop through the list of videos
	foreach ($programCards as $programCard) {
		if (array_key_exists('ProgramCard', $programCard)) {
			$programCard = $programCard['ProgramCard'];
		}
		if (!array_key_exists('PrimaryAssetUri', $programCard)) {
			# Video is not playable - do not show in list(?)
			continue;
		} 
		# Also detech if it is obsolete - ["PrimaryAssetEndPublish"]=> "2013-04-14T12:10:00Z"
		if (array_key_exists('PrimaryAssetEndPublish', $programCard)) {
			if (strtotime($programCard['PrimaryAssetEndPublish']) < time() ) {
				continue;
			}
		}
		#/programcard/imageuri/urn:dr:mu:programcard:529893b06187a20b90e5e196#
		$infoLabels = createInfoLabels($programCard); # Format some information
		$urn=$programCard['Urn'];
		$iconImage="http://www.dr.dk/mu/programcard/imageuri/".$urn."?width=320";
		$moreinfourl = $_SERVER['PHP_SELF']."?slug=". urlencode($programCard['Slug'])."&info=1";
		# Output video
		echo "<div class='w'>";
		echo "<a href='".$moreinfourl."'><img src='".$iconImage."'></a>"; 
		print "<p class='text_line'></p>\n";
		print "<h3><a href='".$moreinfourl."'>".$infoLabels["Title"]."</a></h3>";
		print "<p>".$infoLabels["Subtitle"]."</P>";
		print "<p>Sendt: ".date('j.n.Y \k\l. H:i',$infoLabels["sent"])."</P>";
		print "<p>Udløber: ".date('j.n.Y \k\l. H:i',$infoLabels["expires"])."</P>";
		# Direkte mp4 link redirect
		if (!array_key_exists('PrimaryAssetUri', $programCard)) {
			echo "<p>Video er ikke online endnu</p>";
		} else {
			$url = $_SERVER['PHP_SELF']."?slug=". $programCard['Slug']."&play=1";
			echo "<a href='".$url."'>Afspil video</a></p>";
		}
		# Link til serier
		echo "<hr>";
		$Relation = $api->getRelation("Series", $programCard);
		if ($Relation) {
			$url = $_SERVER['PHP_SELF']."?slug=".urlencode($Relation['Slug']);
			echo "<a href='".$url."'>".$Relation["Slug"]."</a> - ";
		}
		$url = $_SERVER['PHP_SELF']."?field=GenreText&searchtext=".urlencode($infoLabels["GenreText"]);
		print "<a href='".$url."'>".$infoLabels["GenreText"]."</a> - ";
		$url = $_SERVER['PHP_SELF']."?field=OnlineGenreText&searchtext=".urlencode($infoLabels["OnlineGenreText"]);
		print "<a href='".$url."'>".$infoLabels["OnlineGenreText"]."</a>";
		echo "</div>\n";
	}
	return true;
}

function listSingleVideo($api,$programCard) {
	$programCard = $programCard["Data"][0];
	$infoLabels = createInfoLabels($programCard); # Format some information
	echo "<div class='single_video_container'>";
	$urn=$programCard['Urn'];
	$iconImage="http://www.dr.dk/mu/programcard/imageuri/".$urn."?width=640";
	echo "<img src='".$iconImage."'>"; 
	print "<p class='text_line'></p>\n";
	print "<h3>".$infoLabels["Title"]."</h3>";
	print "<p>".$programCard["Subtitle"]."</P>";
	print "<p>".$infoLabels["Description"]."</P>";
	print "<p>Sendt: ".date('j.n.Y \k\l. H:i',$infoLabels["sent"])."</P>";
	print "<p>Udløber: ".date('j.n.Y \k\l. H:i',$infoLabels["expires"])."</P>";
	if (!array_key_exists('PrimaryAssetUri', $programCard)) {
		echo "<p>Video er ikke online endnu</p>";
	} else {
		$asset = $api->_http_request($programCard['PrimaryAssetUri'] );
		$videoUrl = $api->getLink($asset, 'Android');
		$videoUrl = str_replace('rtsp://om.gss.dr.dk/mediacache/_definst_/mp4:content/', 'http://vodfiles.dr.dk/', $videoUrl);
		print "<p><a href='".$videoUrl."'>"."Afspil video"."</A>"."</P>";
	}
	# Link til serier
	echo "<hr>";
	$Relation = $api->getRelation("Series", $programCard);
	if ($Relation) {
		$url = $_SERVER['PHP_SELF']."?slug=".urlencode($Relation['Slug']);
		echo "<a href='".$url."'>".$Relation["Slug"]."</a> - ";
	}
	$url = $_SERVER['PHP_SELF']."?field=GenreText&searchtext=".urlencode($infoLabels["GenreText"]);
	print "<a href='".$url."'>".$infoLabels["GenreText"]."</a> - ";
	$url = $_SERVER['PHP_SELF']."?field=OnlineGenreText&searchtext=".urlencode($infoLabels["OnlineGenreText"]);
	print "<a href='".$url."'>".$infoLabels["OnlineGenreText"]."</a>";
	echo "</div>\n";
	return true;
}

function playVideo($api,$programCard) {
	$programCard = $programCard["Data"][0];
	if (array_key_exists('PrimaryAssetUri', $programCard)) {
		$asset = $api->_http_request($programCard['PrimaryAssetUri'] );
		$videoUrl = $api->getLink($asset, 'Android');
		$videoUrl = str_replace('rtsp://om.gss.dr.dk/mediacache/_definst_/mp4:content/', 'http://vodfiles.dr.dk/', $videoUrl);
		header("Location: $videoUrl");
		die();
	}
	return null;
}

# 
# Construction of the page
#
$slug=$_GET["slug"];
$info=$_GET["info"];
$play=$_GET["play"];
$letter=$_GET["letter"];

$api = new TvApi;

# The play action will trigger a redirect using header and die! - if not possible it will just do nothing.
if ($play) {
	$programCards = $api->programCard($slug);
	playVideo($api, $programCards);
}

showHeader();
showMenu();

# Set default slug
if (!$slug) {
	$slug="mostviewed1";
}

if ($info) {
	# If single video - show that
	$programCards = $api->programCard($slug);
	listSingleVideo($api, $programCards);
} elseif ($_GET["searchtext"] and !$_GET["field"]) { 
	# If search string - do seach
	$search= $_GET["searchtext"];
	$search = stripslashes($search);
	$search = urlencode($search);
	$programCards = $api->searchProgramCards($limit=50, $offset=$_GET["offset"], $field="Title", $searchtext=$search);
	listVideos($api, $programCards, $navbar=true);
} elseif ($_GET["searchtext"] and $_GET["field"]) { 
	# If search string - do seach
	$search= $_GET["searchtext"];
	$search = stripslashes($search);
	$search = urldecode($search);
	$programCards = $api->searchProgramCards($limit=50, $offset=$_GET["offset"], $field=$_GET["field"], $searchtext=$search);
	listVideos($api, $programCards, $navbar=true);
} elseif ($slug=="mostviewed1") {
	# Show most viewed programcards
	$programCards = $api->getMostViewedProgramCards($days=1);
	listVideos($api, $programCards);
} elseif ($slug=="mostviewed7") {
	# Show most viewed programcards
	$programCards = $api->getMostViewedProgramCards($days=7);
	listVideos($api, $programCards);
} elseif ($slug=="mostviewed31") {
	# Show most viewed programcards
	$programCards = $api->getMostViewedProgramCards($days=31);
	listVideos($api, $programCards);
} elseif ($slug=="recent") {
	# Show most viewed programcards
	$programCards = $api->recentProgramCards($count=50, $offset=$_GET["offset"]);
	listVideos($api, $programCards);
} elseif ($slug=="bundles") {
	$programCards = $api->bundlesWithPublicAsset($letter=$letter, $limit=100, $offset=$_GET["offset"]);
	listBundles($api, $programCards);
} elseif (!$info) { 
	# If no search string - use slug
	$programCards = $api->programCardRelations($slug, $limit=50, $offset=$_GET["offset"]);
	listVideos($api, $programCards, $navbar=true);
} 

if ($_GET["debug"]) {
	print "\n<p class='text_line'>&nbsp;</p>\n";
	print "<pre>";
	echo "Get:";
	var_dump($_GET);
	echo "Post:";
	var_dump($_POST);
	print "Data fetched\n";
	var_dump($programCards);
	print "</pre>";
}


?>  
</body> 
</html>
  