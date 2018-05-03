<?php
session_start();
$yenile = @$_SESSION["chat"];

if ($yenile == "yenile") {
	unset($_SESSION["chat"]);
	echo $yenile;
}
?>