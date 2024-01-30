<?php

$host= 'w01d1629.kasserver.com';
$user = 'f01514e4';
$password = 'qzVZHXG6TvtLrrhs';
$ftpConn = ftp_connect($host);
$login = ftp_login($ftpConn,$user,$password);
ftp_set_option($ftpConn, FTP_USEPASVADDRESS, false); // set ftp option
ftp_pasv($ftpConn, true); //make connection to passive mode

// check connection
if ((!$ftpConn) || (!$login)) {
 echo 'FTP connection has failed! Attempted to connect to '. $host. ' for user '.$user.'.';
} else{

 $directory = ftp_nlist($ftpConn,'.');
 $backupPath = ABSPATH . 'wp-content/uploads/hex_files';
 foreach($directory as $ftp_file){
	 if($ftp_file == '.' || $ftp_file == '..')
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
		copy($filename, $backupPath . "/" . $filename);
		ftp_delete($ftpConn, $ftp_file);
		//echo $ftp_file . "<br>";

		echo "<pre>"; print_R($filename); echo "</pre>";
	 }
 }

}
ftp_close($ftpConn);


?>