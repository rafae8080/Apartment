<?php

$serverName = "DESKTOP-F68QS4T";
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => "",
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}


