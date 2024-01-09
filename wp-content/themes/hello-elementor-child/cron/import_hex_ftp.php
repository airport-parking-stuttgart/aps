<?php

$host= 'w01d1629.kasserver.com';
$user = 'f01514e4';
$password = 'EzyXY292jbtXJyk5mKsp';
$ftpConn = ftp_connect($host);
$login = ftp_login($ftpConn,$user,$password);
ftp_set_option($ftpConn, FTP_USEPASVADDRESS, false); // set ftp option
ftp_pasv($ftpConn, true); //make connection to passive mode

// check connection
if ((!$ftpConn) || (!$login)) {
	echo 'FTP connection has failed! Attempted to connect to '. $host. ' for user '.$user.'.';
} 
else{

	$directory = ftp_nlist($ftpConn,'.');
	$backupPath = ABSPATH . 'wp-content/uploads/hex_files';
	$root = ABSPATH;
	$base_url = $_SERVER['HTTP_HOST'];
	
	if($base_url == "airport-parking-stuttgart.de"){
		foreach($directory as $ftp_file){
			if($ftp_file == '.' || $ftp_file == '..' || $ftp_file == 'backup')
				continue;
			else{
				$h = fopen('php://temp', 'r+');
				ftp_fget($ftpConn, $h, $ftp_file, FTP_BINARY, 0);
				$fstats = fstat($h);
				fseek($h, 0);
				$contents = fread($h, $fstats['size']); 
				fclose($h);
				$filename = $ftp_file;
				file_put_contents($filename, $contents);
				//copy($filename, 'hex_files/backup/' . $filename);
				ftp_rename($ftpConn, $ftp_file, 'backup/'.$ftp_file);
				copy($filename, $backupPath . "/" . $filename);
				ftp_delete($ftpConn, $ftp_file);
				wp_delete_file( $root . $filename );
			}
		}
	}
}
ftp_close($ftpConn);
?>