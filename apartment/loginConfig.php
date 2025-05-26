<?php

$serverName = "LAPTOP-0QN98R6Q";
$connectionOptions = array(
    "Database" => "LeaseManagementDB",
    "Uid" => "",
    "PWD" => "",
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}


