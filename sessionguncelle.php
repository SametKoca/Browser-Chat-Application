<?php
session_start();

$isteyenIp = $_POST["isteyenIp"];
$istenenIp = $_POST["istenenIp"];

$_SESSION[$isteyenIp][$istenenIp] = true;
$_SESSION[$istenenIp][$isteyenIp] = true;

?>