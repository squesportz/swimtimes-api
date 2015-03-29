<?php
include "init.php";


if (!file_exists(__DIR__.'/team.data') || filemtime(__DIR__.'/team.data') <= (time()-60*60*24*15)) { // Recheck every 15 days
	/* Get all swimmers */
	try {
		$api->setPath('swim/list/team?team='.TEAM.'&active='.ACTIVE);
		$data = $api->getData(); $swimmers = array();
		foreach ($data as $k => $d) {
			@$swimmers[$d->aRegistration] = $d->aId;
		}
		file_put_contents(__DIR__.'/team.data', serialize($swimmers));
	} catch (Exception $e) {
		echo 'Error: ',  $e->getMessage(), "\n";
		exit;
	}
} else {
	$swimmers = unserialize(file_get_contents(__DIR__.'/team.data'));
}

function calcAid($i) {
	global $swimmers;
	return (isset($swimmers[$i]) ? $swimmers[$i] : 0);
}

$aRegistration = (isset($_REQUEST['aRegistration']) ? $_REQUEST['aRegistration'] : 0);



?><html>

<body>
<?php
try {
	?><form name='swimmers' method='get'><select onchange='document.swimmers.submit();' name='aRegistration'><?php
	$api->setPath('swim/list/team?team='.TEAM.'&active='.ACTIVE);
	$data = $api->getData();
		echo "<option value='0'".($aRegistration == 0 ? " selected='selected'" : '').">Selecteer zwemmer</option>";
		foreach ($data as $row => $d) {
			echo "<option value='".$d->aRegistration."'".($aRegistration == $d->aRegistration ? " selected='selected'" : '').">".$d->aRegistration." - ".$d->aFirstName." ".$d->aMiddleName." ".$d->aNamePrefix." ".$d->aLastName."</option>";
			if ($aRegistration == $d->aRegistration) $name = $d->aFirstName." ".$d->aMiddleName." ".$d->aNamePrefix." ".$d->aLastName;
		}

	?></select></form><?php 
} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
}


if ($aRegistration > 0) { 
	try {
		?><h1>Persoonlijke records van <?php echo $name; ?></h1>
		<table width='100%'>
		<tr><td><strong>Slag</strong></td><td><strong>Baan</strong></td><td><strong>Tijd</strong></td><td><strong>Datum</strong></td><td><strong>Wedstrijdnaam</strong></td><td><strong>Plaats (Land)</strong></td></tr>
		<?php
		$api->setPath('swim/list/records?id='.calcAid($aRegistration));
		$data = $api->getData();
		foreach ($data as $row => $d) {
			echo "<tr>";
			echo "<td>".calcStyle($d->swimid)."</td>";
			echo "<td>".($d->mCourse == 1 ? '50m' : '25m')."</td>";
			echo "<td>".calcTime($d->resulttime/1000)."</td>";
			echo "<td>".calcDate($d->date)."</td>";
			echo "<td>".$d->mName."</td>";
			echo "<td>".$d->mCity." (".$d->mNation.")</td>";
			echo "</tr>";
		}
		?></table><?php 
	} catch (Exception $e) {
		echo 'Error: ',  $e->getMessage(), "\n";
	}
}
?><i>Data prepared by <a href='//www.swimtimes.nl/'>SwimTimes</a>.</i></body>

</html>