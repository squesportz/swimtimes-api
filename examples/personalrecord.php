<?php
include "init.php";

$aId = (isset($_REQUEST['aId']) ? $_REQUEST['aId'] : 0);
?><html>

<body>
<?php
try {
	?><form name='swimmers' method='post'><select onchange='document.swimmers.submit();' name='aId'><?php
	$api->setPath('swim/list/team?team='.TEAM.'&active='.ACTIVE);
	$data = $api->getData();
		echo "<option value='0'".($aId == 0 ? " selected='selected'" : '').">Selecteer zwemmer</option>";
		foreach ($data as $row => $d) {
			echo "<option value='".$d->aId."'".($aId == $d->aId ? " selected='selected'" : '').">".$d->aRegistration." - ".$d->aFirstName." ".$d->aMiddleName." ".$d->aNamePrefix." ".$d->aLastName."</option>";
			if ($aId == $d->aId) $name = $d->aFirstName." ".$d->aMiddleName." ".$d->aNamePrefix." ".$d->aLastName;
		}

	?></select></form><?php 
} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
}


if ($aId > 0) { 
	try {
		?><h1>Persoonlijke records van <?php echo $name; ?></h1>
		<table width='100%'>
		<tr><td><strong>Slag</strong></td><td><strong>Baan</strong></td><td><strong>Tijd</strong></td><td><strong>Datum</strong></td><td><strong>Wedstrijdnaam</strong></td><td><strong>Plaats (Land)</strong></td></tr>
		<?php
		$api->setPath('swim/list/records?id='.$aId);
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