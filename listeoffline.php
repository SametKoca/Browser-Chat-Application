<?php
$file = fopen("offline.txt","r");
$metin = "";
while(!feof($file)) {
	$satir = fgets($file);
	$metin.= $satir;
}
fclose($file);
$file2 = fopen("online.txt", "w");
fwrite($file2, $metin);
fclose($file2);
?>
