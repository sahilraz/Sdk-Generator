<?php 
$game_param = 5;
$game_name = 'wingo';
require_once __DIR__ . '/quantum-connect.php';

if (!$apiFetchOk) {
    die("API Error or no items found in the data list");
}

	if ($toggle == 1) {
		// 1 = OFF: Normal result
		$manualQuery = $conn->query("SELECT sankhye FROM hastacalita_phalitansa_funf WHERE sthiti='1' LIMIT 1");
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
		// 0 = ON: WinGo API ka real result
		$yadrcchika = $kadimesucyanka = isset($apidata['number']) ? $apidata['number'] : rand(0, 9);
		$banna = isset($apidata['color']) ? $apidata['color'] :
				($yadrcchika == 0 ? 'red,violet' :
				($yadrcchika == 5 ? 'green,violet' :
				(in_array($yadrcchika, [1,3,7,9]) ? 'green' : 'red')));
		$yadrcchikasankhye = isset($apidata['premium']) ? $apidata['premium'] :
					(int)(rand(1,9).rand(1,9).rand(1,9).rand(1,9).$yadrcchika);
	}
	$dinanka = date('Y-m-d H:i:s');
	$samasyesreni['atadaaidi']=$apidata['issueNumber'];

		// RACE CONDITION FIX: MySQL named lock se ensure karo sirf ek process chale
		$lockName = 'funf_period_' . $samasyesreni['atadaaidi'];
		$lockResult = mysqli_query($conn, "SELECT GET_LOCK('$lockName', 0)");
		$lockRow = mysqli_fetch_row($lockResult);
		if (!$lockResult || $lockRow[0] != 1) { exit; } // Dusra process already chal raha hai

		// Duplicate check: agar ye period already process ho chuka hai to skip karo
		$dupCheck = mysqli_query($conn, "SELECT kalaparichaya FROM gellaluhogiondu_phalitansa_funf WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' LIMIT 1");
		if (mysqli_num_rows($dupCheck) > 0) {
			mysqli_query($conn, "SELECT RELEASE_LOCK('$lockName')");
			exit;
		}
		$gadhipathuli = "SELECT ojana, ketebida
		  FROM bajikattuttate_funf
		  WHERE kalaparichaya = ".$samasyesreni['atadaaidi']."
		  ORDER BY parichaya DESC LIMIT 1";
		$gadhipathuliphala = $conn->query($gadhipathuli);
		$gadhipathulidhadi = mysqli_num_rows($gadhipathuliphala);
		
		if($gadhipathulidhadi >= 1){
			
			$tathya = mysqli_query($conn,"INSERT INTO `gellaluhogiondu_phalitansa_funf` (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) VALUES ('".$samasyesreni['atadaaidi']."','".$yadrcchikasankhye."','".$kadimesucyanka."','".$banna."','uncensored','".$dinanka."')");
			
			if($kadimesucyanka == 0){
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '0'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '0' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '1'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '1' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '2'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '2' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '3'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '3' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '4'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '4' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '5'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '5' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '6'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '6' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '7'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '7' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '8'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '8' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
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
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '9'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '9' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_funf set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_funf
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			$nabikarana_dui = "UPDATE bajikattuttate_funf set ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."'";
			$conn->query($nabikarana_dui);
		}
		else{
			
			$tathya = mysqli_query($conn,"INSERT INTO `gellaluhogiondu_phalitansa_funf` (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) VALUES ('".$samasyesreni['atadaaidi']."','".$yadrcchikasankhye."','".$yadrcchika."','".$banna."','shonu','".$dinanka."')");
		}
		$tarika = date('Y-m-d H:i:s'); // Current timestamp
		// gelluonduhogu_funf mein current issueNumber store karo (niyamitakelasa check ke liye)
    $tathya1 = mysqli_query($conn, "INSERT INTO `gelluonduhogu_funf` (`atadaaidi`, `dinankavannuracisi`) VALUES ('" . $samasyesreni['atadaaidi'] . "', '$tarika')");
		mysqli_query($conn, "SELECT RELEASE_LOCK('$lockName')"); // Lock release karo
		
?>