<?php

require_once "../SqueSportz/SwimTimes/connector.class.php";

// Initial settings
define("USERNAME", "***");		// Username for API
define("PASSWORD", "***");		// Password for API
define("TEAM", "***");			// Nation.clubcode or Unique ID
define("ACTIVE", "true");		// Select only active swimmers

$api = new \SqueSportz\SwimTimes\connector();
$api->setAuth(USERNAME, PASSWORD);

function calcTime($Time) {
	if (strpos($Time, '.') || strpos($Time, ',')) { /* Mistake because of localisation */
		$seconden = intval($Time);
		$duizenste = substr(intval($Time*100), -2);
	} else {
		$seconden = intval($Time);
		$duizenste = 0;
	}
	$duizenste=str_pad(intval($duizenste), 2, "0", STR_PAD_LEFT);
	if (intval($seconden/60)>0) {
		$minuten = intval($seconden/60);
		if (intval($minuten/60)>0) {
			$minutes_seconds = intval($minuten/60).":".(str_pad(($minuten%60), 2, "0", STR_PAD_LEFT)).":".(str_pad(($seconden%60), 2, "0", STR_PAD_LEFT));
		} else {
			$minutes_seconds = $minuten.":".(str_pad(($seconden%60), 2, "0", STR_PAD_LEFT));
		}
	} else {
		$minutes_seconds = $seconden;
	}
	$Time = $minutes_seconds.".".$duizenste;
	return $Time;
}

function calcDate($date) {
	list($year, $month, $day) = explode("-", $date);
	return "$day-$month-$year";
}

if (!file_exists(__DIR__.'/styles.json') || filemtime(__DIR__.'/styles.json') <= (time()-60*60*24*15)) { // Recheck every 15 days
	function swimStroke($i) {
			if ($i == 0) return 'Wisselslag';
		elseif ($i == 1) return 'Vlinderslag';
		elseif ($i == 2) return 'Rugslag';
		elseif ($i == 3) return 'Schoolslag';
		elseif ($i == 4) return 'Vrije slag';
	}
	/* Get all the strokes */
	try {
		$api->setPath('styles/all');
		$data = $api->getData(); $styles = array();
		foreach ($data as $k => $d) {
			@$styles[$d->swimid] = ($d->swimcount > 1 ? $d->swimcount." x " : "").$d->swimdistance."m ".swimStroke($d->swimstroke);
		}
		file_put_contents(__DIR__.'/styles.json', json_encode($styles, JSON_PRETTY_PRINT));
	} catch (Exception $e) {
		echo 'Error: ',  $e->getMessage(), "\n";
		exit;
	}
}

$styles = json_decode(file_get_contents(__DIR__.'/styles.json'));
function calcStyle($i) {
	global $styles;
	return (isset($styles->$i) ? $styles->$i : 'Onbekend');
}