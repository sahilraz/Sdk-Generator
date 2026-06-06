<?php
/**
 * WinGo 30s result cron — needs mysqli $conn.
 * CLI/cron: either place conn.php beside this file (mysqli $conn), or wrap this file in another script that defines $conn first.
 */
if (!isset($conn)) {
	$_cron_db = __DIR__ . '/conn.php';
	if (is_file($_cron_db)) {
		require_once $_cron_db;
	}
}
if (!isset($conn)) {
	$err = 'nayakaphalitansa_mulaka_unohs_zehn: $conn missing. Add conn.php (mysqli) in same folder or require your DB file before this script.';
	if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
		fwrite(STDERR, $err . PHP_EOL);
	} else {
		header('Content-Type: text/plain; charset=utf-8', true, 500);
		echo $err;
	}
	exit(1);
}

$game_name = 'wingo';
$game_param = 30;
require_once __DIR__ . '/quantum-connect.php';
			// TOGGLE (game_settings_zehn.toggle): 0 = use external API number/colour/premium; 1 = ignore API digits — use manual table or random
			$toggleQuery = $conn->query("SELECT toggle FROM game_settings_zehn WHERE id = 1 LIMIT 1");
			$toggleRow = ($toggleQuery && mysqli_num_rows($toggleQuery) > 0) ? $toggleQuery->fetch_assoc() : null;
			$toggle = ($toggleRow) ? (int)$toggleRow['toggle'] : 0;

			// toggle=1 OR API fail => manual/random path (skip nahi hoga).
			if ($toggle == 1 || !$apiFetchOk) {
				// Manual / random result path
				// Pehle hastacalita_phalitansa_zehn check karo (manual set number)
				$manualQuery = $conn->query("SELECT sankhye FROM hastacalita_phalitansa_zehn WHERE sthiti='1' LIMIT 1");
				if ($manualQuery && mysqli_num_rows($manualQuery) > 0) {
					$manualRow = $manualQuery->fetch_assoc();
					$yadrcchika = $kadimesucyanka = (int)$manualRow['sankhye'];
				} else {
					$yadrcchika = $kadimesucyanka = rand(0, 9);
				}
				if($yadrcchika == 0){ $banna = 'red,violet'; }
				else if($yadrcchika == 5){ $banna = 'green,violet'; }
				else if($yadrcchika == 1 || $yadrcchika == 3 || $yadrcchika == 7 || $yadrcchika == 9){ $banna = 'green'; }
				else if($yadrcchika == 2 || $yadrcchika == 4 || $yadrcchika == 6 || $yadrcchika == 8){ $banna = 'red'; }
				$yadrcchikasanke = array_fill(0, 4, null);
				for ($i = 0; $i < 4; $i++) { $yadrcchikasanke[$i] = rand(1, 9); }
				$yadrcchikasanke[] = $yadrcchika;
				$yadrcchikasankhye = (int)implode('', $yadrcchikasanke);
			} else {
				// API result path (toggle 0)
				$yadrcchika = $kadimesucyanka = isset($apidata['number']) ? (int)$apidata['number'] : rand(0, 9);
				$banna = isset($apidata['color']) ? $apidata['color'] :
						($yadrcchika == 0 ? 'red,violet' :
						($yadrcchika == 5 ? 'green,violet' :
						(in_array($yadrcchika, [1,3,7,9], true) ? 'green' : 'red')));
				if (isset($apidata['premium']) && $apidata['premium'] !== '') {
					$yadrcchikasankhye = $apidata['premium'];
				} else {
					$yadrcchikasanke = array_fill(0, 4, null);
					for ($i = 0; $i < 4; $i++) {
						$yadrcchikasanke[$i] = rand(1, 9);
					}
					$yadrcchikasanke[] = $yadrcchika;
					$yadrcchikasankhye = (int)implode('', $yadrcchikasanke);
				}
			}
					$dinanka = date('Y-m-d H:i:s');
					$apiIssue = isset($apidata['issueNumber']) ? preg_replace('/\D/', '', (string) $apidata['issueNumber']) : '';
					$localIssue = '';
					$localIssueRes = $conn->query("SELECT atadaaidi FROM gelluonduhogu_zehn ORDER BY kramasankhye DESC LIMIT 1");
					if ($localIssueRes && mysqli_num_rows($localIssueRes) > 0) {
						$localIssueRow = $localIssueRes->fetch_assoc();
						$localIssue = preg_replace('/\D/', '', (string) ($localIssueRow['atadaaidi'] ?? ''));
					}
					// Prefer local open period for settlement; API issue can drift and create stuck rows.
					$samasyesreni['atadaaidi'] = ($localIssue !== '') ? $localIssue : $apiIssue;
					$issueId = $samasyesreni['atadaaidi'];
					if ($issueId === '') {
						exit;
					}
					$alreadySettled = false;
					$settledCheck = $conn->query("SELECT 1 FROM bajikattuttate_zehn WHERE kalaparichaya = '" . $conn->real_escape_string($issueId) . "' AND TRIM(COALESCE(CAST(ergebnis AS CHAR), '')) <> '' LIMIT 1");
					if ($settledCheck && mysqli_num_rows($settledCheck) > 0) {
						$alreadySettled = true;
					}

        $gadhipathuli = "SELECT ojana, ketebida
          FROM bajikattuttate_zehn
          WHERE kalaparichaya = ".$samasyesreni['atadaaidi']."
          ORDER BY parichaya DESC LIMIT 1";
        $gadhipathuliphala = $conn->query($gadhipathuli);
        $gadhipathulidhadi = ($gadhipathuliphala ? mysqli_num_rows($gadhipathuliphala) : 0);
        
        if($gadhipathulidhadi >= 1 && !$alreadySettled){
			
			
			$tathya = mysqli_query($conn,"INSERT INTO `gellaluhogiondu_phalitansa_zehn` (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) SELECT '".$samasyesreni['atadaaidi']."','".$yadrcchikasankhye."','".$kadimesucyanka."','".$banna."','uncensored','".$dinanka."' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `gellaluhogiondu_phalitansa_zehn` WHERE `kalaparichaya` = '".$samasyesreni['atadaaidi']."' LIMIT 1)");
			
			if($kadimesucyanka == 0){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '0'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '0' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 1){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '1'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '1' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 2){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '2'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '2' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 3){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '3'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '3' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 4){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '4'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '4' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 5){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '5'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '5' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 6){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '6'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '6' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 7){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '7'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '7' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 8){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '8'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '8' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 9){
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '9'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '9' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			$nabikarana_dui = "UPDATE bajikattuttate_zehn set ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."'";
			$conn->query($nabikarana_dui);
		}
        else{        
                        $tathya = mysqli_query($conn,"INSERT INTO `gellaluhogiondu_phalitansa_zehn` 
                            (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) 
                            SELECT '".$samasyesreni['atadaaidi']."','".$yadrcchikasankhye."','".$yadrcchika."','".$banna."','shonu','".$dinanka."' FROM DUAL
                            WHERE NOT EXISTS (SELECT 1 FROM `gellaluhogiondu_phalitansa_zehn` WHERE `kalaparichaya` = '".$samasyesreni['atadaaidi']."' LIMIT 1)");
        }
           $tarika = date('Y-m-d H:i:s'); // Current timestamp
		$parabartikrama = $samasyesreni['atadaaidi'] + 1;
    $tathya1 = mysqli_query($conn, "INSERT INTO `gelluonduhogu_zehn` (`atadaaidi`, `dinankavannuracisi`) SELECT '$parabartikrama', '$tarika' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `gelluonduhogu_zehn` WHERE `atadaaidi` = '$parabartikrama' LIMIT 1)");
    
    ?>