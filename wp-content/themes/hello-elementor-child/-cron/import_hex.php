<?php
$base_url = $_SERVER['HTTP_HOST'];
if($base_url == "airport-parking-stuttgart.de"){
	$db = Database::getInstance();
	$db->importHEX_NEW();
}


?>