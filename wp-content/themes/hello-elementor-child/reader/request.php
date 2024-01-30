<?php
	/******************************************************************************************
	MCR08 Card Reader Family, Server side example Web service based on GET method and PHP
	Minova Technology 2018
	*******************************************************************************************/
	
	date_default_timezone_set('Europe/Berlin');	
	$timeDiff = 2;																						//Time diff, if server time differs from local time on site, set to 0 if server and device are at the same time zone
	$unixTime = time() + $timeDiff * 3600;																//Prepare time variable for responses		
	$datetime = date("Y-m-d H:i");	
	if(isset($_GET['devID']))
	{
		$devID = $_GET['devID'];
		if(isset($_GET['UID']))
		{
			$uid = $_GET['UID'];
			//echo $devID.',ACK;Check-In!;'.$datetime.',TSYNC='.$unixTime.'';
			$url = "https://airport-parking-stuttgart.de/curl/?request=frid&pw=apg_req54894135";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			http_build_query(array(
				 'data' => $_GET,
				 'date' => $datetime
				 
			)));
			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close($ch);
			
			echo $devID.',ACK;'.$server_output.';Check-In!;'.$datetime.',TSYNC='.$unixTime.'';
		}
		
	}		
	
		
?>


	
