<?php
/**
 * Created by PhpStorm.
 * User: iRevive
 * Date: 23.02.14
 * Time: 23:17
 */


header('Content-Type: application/json');

ini_set('display_errors',1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Kiev');
$host = 'localhost';
$user = 'user';
$pwd = 'password';
$db = 'adrenaline';

$loc = $_GET["loc"];
$objId = $_GET["objId"];
$npcId = $_GET["npcId"];
$npcLevel = $_GET["npcLevel"];
$x = $_GET["x"];
$y = $_GET["y"];
$z = $_GET["z"];
$uploader = $_GET["uploader"];
$drop = $_GET["drop"];
$dead = $_GET["dead"];

$dateFormat = "Y-m-d H:i:s";

$link = mysql_connect($host, $user, $pwd);

if (!$link or !mysql_select_db($db)) {
    die('MySql error: ' . mysql_error());
}

if ($dead == "false") {
	addSpawn($loc, $objId, $npcId, $npcLevel, $x, $y, $z, $uploader, $drop);
} else
	updateSpawn($loc, $objId, $npcId, $npcLevel, $x, $y, $z, $uploader, $drop);

function getSpawn($time) {
    $query = "SELECT `spawn_time`, `objId`, `location` FROM `chests_spawn`";
	
	$dictionary = queryAsDictionary($query);
	
	$timeComparator = function($key, $value) {
		return ($key == 'spawn_time' and (date($GLOBALS['dateFormat'], strtotime($value)) - date($GLOBALS['dateFormat'])) >= -2);
	};
	
	print_r(getValuesByFunctor($dictionary, $timeComparator));
	
}

function addSpawn($loc, $objId, $npcId, $npcLevel, $x, $y, $z, $uploader, $drop) {
	$selectQuery = "SELECT `objId` FROM `chests_spawn`";
	$selectResult = getValues($selectQuery);
	if (!in_array($objId, $selectResult)) {
		$currTime = date($GLOBALS['dateFormat']);
		$userIp = getRealIp();
		$query = "INSERT INTO `chests_spawn` (`objId`, `spawn_time`, `location`, `npcId`, `npcLevel`, `x`, `y`, `z`, `drop`, `uploader`, `ip`) 
									VALUES ('$objId', '$currTime', '$loc', '$npcId', '$npcLevel', '$x', '$y', '$z', '$drop','$uploader', '$userIp')";
		$result = mysql_query($query) or die('MySql error: ' . mysql_error());
		if ($result) 
			die('Spawn added');
	} else {
		die('Contains this objId');
	}
}

function updateSpawn($loc, $objId, $npcId, $npcLevel, $x, $y, $z, $uploader, $drop) {
	$selectQuery = "SELECT `objId` FROM `chests_spawn`";
	$selectResult = getValues($selectQuery);
	$currTime = date($GLOBALS['dateFormat']);
	if (in_array($objId, $selectResult)) {
		$query = "UPDATE `chests_spawn` SET `decoy_time`='$currTime', `drop`='$drop' WHERE `objId`='$objId'";
		$result = mysql_query($query) or die('MySql error: ' . mysql_error());
		if ($result)
			die('Updated');
	} else {
		$userIp = getRealIp();
		$query = "INSERT INTO `chests_spawn` (`objId`, `spawn_time`, `location`, `npcId`, `npcLevel`, `x`, `y`, `z`, `drop`, `decoy_time`, `uploader`, `ip`) 
									VALUES ('$objId', '$currTime', '$loc', '$npcId', '$npcLevel', '$x', '$y', '$z', '$drop', '$currTime', '$uploader', '$userIp')";
		$result = mysql_query($query) or die('MySql error: ' . mysql_error());
		if ($result) 
			die('Updated');
	}
}

function jsonSplit($query, $start, $end) {
    $result = mysql_query($query);

    $rows = array();
	
    while ($r = mysql_fetch_assoc($result)) {
        $rows[] = $r;
    }
	
    $data = json_encode($rows);
    $data = substr($data, $start);
    $data = substr($data, 0, strlen($data) - $end);

    return $data;
}

function getRealIp() {
 if (!empty($SERVER['HTTP_CLIENT_IP'])) 
 {
   $ip=$SERVER['HTTP_CLIENT_IP'];
 }
 elseif (!empty($SERVER['HTTP_X_FORWARDED_FOR']))
 {
  $ip=$SERVER['HTTP_X_FORWARDED_FOR'];
 }
 else
 {
   $ip=$SERVER['REMOTE_ADDR'];
 }
 return $ip;
}

//h:i:s m-d-y
function getUnixTime($stringTime) {
	$timeDate = explode(" ", $stringTime);
	list($hours, $minutes, $seconds) = explode(":", $timeDate[0]);
	list($year, $month, $day) = explode("-", $timeDate[1]);
	
	return mktime($hours, $minutes, $seconds, $month, $day, $year);
}

//query funcs
function getValues($query) {
	$result = mysql_query($query) or die('MySql error');
	$valuesArray = array();
	while($row = mysql_fetch_assoc($result)) {
		foreach($row as $cname => $cvalue) {
			array_push($valuesArray, $cvalue);
		}
	}
	return $valuesArray;
}

function getKeys($query) {
	$result = mysql_query($query) or die('MySql error');
	$keysArray = array();
	while($row = mysql_fetch_assoc($result)) {
		foreach($row as $cname => $cvalue) {
			array_push($keysArray, $cname);
		}
	}
	return $keysArray;
}

function queryAsDictionary($query) {
	$result = mysql_query($query) or die('MySql error');
	$dictionary = array();
	while($row = mysql_fetch_assoc($result)) {
		$values = array();
		foreach($row as $cname => $cvalue) {
			$values[$cname] = $cvalue;
		}
		array_push($dictionary, $values);
		unset($values);
	}
	return $dictionary;
}

//dictionary funcs
function getValuesByFunctor($dictionary, $functor) {
	$result = array();
	foreach ($dictionary as $data) {
		foreach ($data as $key => $value) {
			if ($functor($key, $value)) {
				array_push($result, $value);
			}
		}
	}
	return $result;
}

mysql_close($link);
?>