<?php

/**
 * @Author iRevive
 * @Date: 15.01.2015
 * @Time: 23:17
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
$timeToSpawn = $_GET["tospawn"];

$dateFormat = "H:i:s d-m-y";

$link = mysql_connect($host, $user, $pwd);

if (!$link or !mysql_select_db($db)) {
    die('MySql error: ' . mysql_error());
}

$date = DateTime::createFromFormat($GLOBALS['dateFormat'], '23:41:15 14-01-15');
$currentDate = new DateTime('now');

$nowPlus2 = new DateTime('now');
$nowPlus2->add(new DateInterval('PT2H'));
$diff = $nowPlus2->diff($currentDate);

if ($timeToSpawn == 'alive') {
	echo getSpawn($loc);
} else if (is_numeric($timeToSpawn)) {
	echo getSoonSpawnCount($loc, $timeToSpawn);
}
die();

function getSpawn($loc) {
    $query = "SELECT `x`, `y`, `z` FROM `chests_spawn` WHERE `location`='$loc' and `drop`='alive' LIMIT 1";
	
	$result = getValues($query);
	
	if ((count($result) == 0) or ($result[0] == 0))
		return 'Nope';
	else
		return "$result[0];$result[1];$result[2]";	
}

function getSoonSpawnCount($loc, $timeToSpawn) {
	//+2 часа - корректировка времени по Киеву; $timeToSpawn - время до респа, в секундах
	$query = "	SELECT COUNT(`id`) as Amount 
				FROM `chests_spawn`
				WHERE 
				(UNIX_TIMESTAMP(`decoy_time` + INTERVAL 2 HOUR) - UNIX_TIMESTAMP(NOW() + INTERVAL 2 HOUR) > 0
				and UNIX_TIMESTAMP(`decoy_time` + INTERVAL 2 HOUR) - UNIX_TIMESTAMP(NOW() + INTERVAL 2 HOUR) < '$timeToSpawn'
				and `location`='$loc')";
	
	$result = getValues($query);
	return $result[0];
}

function getRealIp() {
 if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
 {
   $ip=$_SERVER['HTTP_CLIENT_IP'];
 }
 elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
 {
  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
 }
 else
 {
   $ip=$_SERVER['REMOTE_ADDR'];
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