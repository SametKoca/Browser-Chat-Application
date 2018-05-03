<?php
    session_start();

    $isteyenIp = $_POST["chatIsteyenIp"];
    $istenenIp = $_POST["chatIstenenIp"];

    $_SESSION["chat"] = "yenile";

    unset($_SESSION[$isteyenIp][$istenenIp]);
    unset($_SESSION[$istenenIp][$isteyenIp]);
?>