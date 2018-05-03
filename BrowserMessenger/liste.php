<?php
session_start();
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<table width="495" border="0" name="tableList" class="table">
  <tr>
    <td><strong>Resim</strong></td>
    <td><strong>Nick</strong></td>
    <td><strong>IP Adresi</strong></td>
    <td><strong>Durum</strong></td>
    <td><strong>Sohbet</strong></td>
  </tr>
  <?php
  $file = fopen("online.txt","r");
  $ip = $_SESSION["ip"];
  while(!feof($file)) {
    $satir = fgets($file);
    $bol = explode("\r\n", $satir);
    for($i=0; $i < count($bol) - 1; $i++) {
      $bol2 = explode("-", $bol[$i]);
      $digerIp = $bol2[1];
      $filename = $digerIp.".jpeg";
      $color = "success";
      if($bol2[2] == "Online")
        $color = "success";
      else if($bol2[2] == "Offline")
        $color = "danger";
      
      echo '<tr class="'.$color.'">
      

      <td>';
      
      if (file_exists($filename)){

        echo '<img src="'.$filename.'" alt="" border=3 height=20 width=20></img></td>';
      }else{ 
        echo '<img src="msn.png" alt="" border=3 height=20 width=20></img></td>';
      }

      

      echo '<td>'.$bol2[0].'</td>
      <td>'.$bol2[1].'</td>
      <td>'.$bol2[2].'</td>
      <td>';
      if ($bol2[1] == $_SESSION["ip"] || $bol2[2] == "Offline") 
        echo '</td>';
      else if($bol2[1] != $_SESSION["ip"] && (!isset($_SESSION[$ip][$digerIp]) || !$_SESSION[$ip][$digerIp]))
        echo '<input type="button" value="Sohbet Et" onclick="izin(\''.$_SESSION["nick"].'\', \''.$_SESSION["ip"].'\', \''.$bol2[0].'\', \''.$bol2[1].'\')"></td>';
      else if($bol2[1] != $_SESSION["ip"] && isset($_SESSION[$ip][$digerIp]) && $_SESSION[$ip][$digerIp] && isset($_SESSION[$digerIp][$ip]) && $_SESSION[$digerIp][$ip])
        echo '<input type="button" value="Sohbete Başla" onclick="basla(\''.$_SESSION["nick"].'\', \''.$_SESSION["ip"].'\', \''.$bol2[0].'\', \''.$bol2[1].'\')"></td>
      </tr>';
    }
  }
  ?>
</table>
<form action="cikis.php" method="POST">
  <input type="submit" name="disconnect" value="Oturumu Kapat">
</form>