<?php
$con = mysqli_connect(
	$conf['databaseHost'], 
	$conf['databaseUsername'], 
	$conf['databasePassword']
	);

if (mysqli_connect_errno()) {
	die('mysqli connection error: ' . mysqli_connect_error());
}

mysqli_query($con, "set character set 'utf8'");
mysqli_query($con, "set names 'utf8'");
//echo 'Success...' . mysqli_get_host_info($con) . "<br />";

?>