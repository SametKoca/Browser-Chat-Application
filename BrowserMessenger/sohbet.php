<?php
session_start();
?>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
	var wsUri = "ws://192.168.43.120:3500/BrowserMessenger/4/server.php"; //Write server.php path with your ip adress
	websocket = new WebSocket(wsUri);

  function izin(isteyenUser, isteyenIpA, istenenUser, istenenIpA) {
    
    var izinIstegi = {
      tip: 'izin',
      isteyenNick: isteyenUser,
      isteyenIp: isteyenIpA,
      istenenNick: istenenUser,
      istenenIp: istenenIpA
    };
    websocket.send(JSON.stringify(izinIstegi));
  }

  function basla(isteyenUser, isteyenIpA, istenenUser, istenenIpA) {
    window.open("chat.php?isteyenIp=" + isteyenIpA + "&isteyenNick=" + isteyenUser + "&istenenNick=" + istenenUser + "&istenenIp=" + istenenIpA, "_blank");
  }

  function sendImg(data,rip) {
    var istek = {
      tip: 'resim',
      rip: rip,
      imageCode: data
    };
    
    websocket.onopen = () => websocket.send(JSON.stringify(istek));
    location.reload();
  }

  $(document).ready(function(){

    function izinVer(izinResult, isteyen, istenen, istenenUser, isteyenUser) {
      var istek = {
        tip: 'izincevap',
        isteyenIp: isteyen,
        istenenIp: istenen,
        isteyenNick: isteyenUser,
        istenenNick: istenenUser,
        cevap: izinResult
      };
      websocket.send(JSON.stringify(istek));
    }

    websocket.onmessage = function(ev) {
      var msg = JSON.parse(ev.data);
      if(msg.tip == "izin") {
       var result = confirm(msg.isteyenNick + " kullanıcısı sizinle sohbet etmek istiyor. İzin veriyor musunuz?");
       izinVer(result, msg.isteyen, msg.istenen, msg.istenenNick, msg.isteyenNick);
       if (!result)
        location.reload();
    }
    if(msg.tip == "izinverildi") {
      $.ajax({
        type: "POST",
        data: "isteyenIp=" + msg.isteyen + "&istenenIp=" + msg.istenen,
        url: "sessionguncelle.php",
        success:function(result) {
          window.open("chat.php?isteyenIp=" + msg.isteyen + "&isteyenNick=" + msg.isteyenNick + "&istenenNick=" + msg.istenenNick + "&istenenIp=" + msg.istenen);
        }
      });
    }
    if(msg.tip == "izinverilmedi") {
      alert(msg.istenenNick + " kullanıcısı izin isteğini onaylamadığı için sohbet başlatılamadı.");
      location.reload();
    }
    if(msg.tip == "yenile") {
      
      location.reload();
      alert("Geldi");
    }
  };

  setInterval(function() {
    $.ajax({ 
      type:"POST",
      url:"liste.php",
      success:function(result) {
        $("#userList").html(result);
      }
    });
  }, 2000);

  setInterval(function() {
    $.ajax({ 
      type:"POST",
      url:"yenilekontrol.php",
      success:function(result) {
        if (result == "yenile") {
          location.reload();
        }
      }
    });
  }, 1000);

  setInterval(function() {
    $.ajax({ 
      type:"POST",
      url:"listeoffline.php",
      success:function(result) {
      }
    });
  }, 15000);
});
</script>

<?php


if (isset($_POST["submit"]) && !$_SESSION["postrequest"]) {
  $_SESSION["postrequest"] = true;
  $user = trim($_POST["nick"]);
  $check = $_FILES["fileToUpload"]["tmp_name"];
  
  $_SESSION["nick"] = $user;
  $ip = $_SERVER["REMOTE_ADDR"]; 
  $_SESSION["ip"] = $ip;
  $_SESSION["oturum"] = true;
  
  if($check !== false && $_FILES["fileToUpload"]["name"] != '') {
    $file_tmp= $_FILES['fileToUpload']['tmp_name'];
    $type = pathinfo($file_tmp, PATHINFO_EXTENSION);
    $data = file_get_contents($file_tmp);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    echo '<script>sendImg("'.$base64.'","'.$ip.'");</script>';
  }
  $file = fopen("online.txt","a+");
  $file4 = fopen("offline.txt","a+");
  $kayitVarmi = false;
  $kayitVarmi2 = false;
  $eskiNick = "";
  $metin = "";
  $durum = "";
  while(!feof($file)) {
    $satir = fgets($file);
    $metin.=$satir;
    $bol = explode("\r\n", $satir);
    for($i=0; $i < count($bol) - 1; $i++) {
      $bol2 = explode("-", $bol[$i]);
      if ($bol2[1] == $ip && $bol2[0] == $user) {
        $kayitVarmi = true;
        $durum = $bol2[2];
      }
      else if($bol2[1] == $ip){
        $kayitVarmi2 = true;

        $eskiNick = $bol2[0];
        $durum = $bol2[2];
      }
    }
  }
  if($kayitVarmi2){
    $replaceMetinEski = "";
    $replaceMetinYeni = "";
    if($durum == "Online") {
      $replaceMetinEski = $eskiNick."-".$_SESSION["ip"]."-Online";
      $replaceMetinYeni = $_SESSION["nick"]."-".$_SESSION["ip"]."-Online";
    }
    else {
      $replaceMetinEski = $eskiNick."-".$_SESSION["ip"]."-Offline";
      $replaceMetinYeni = $_SESSION["nick"]."-".$_SESSION["ip"]."-Online";
    }
    $metin = str_replace($replaceMetinEski, $replaceMetinYeni, $metin);
    $file2 = fopen("online.txt","w+");
    $file3 = fopen("offline.txt","w+");
    fwrite($file2, $metin);
    fwrite($file3, $metin);
    fclose($file2);
    fclose($file3);
  }
  else if (!$kayitVarmi) {
    fwrite($file, $user);
    fwrite($file, '-');
    fwrite($file, $ip);
    fwrite($file, '-');
    fwrite($file, 'Online');
    fwrite($file, "\r\n");

    fwrite($file4, $user);
    fwrite($file4, '-');
    fwrite($file4, $ip);
    fwrite($file4, '-');
    fwrite($file4, 'Online');
    fwrite($file4, "\r\n");
  }

  else {
    $replaceMetinEski = "";
    $replaceMetinYeni = "";
    if($durum == "Online") {
      $replaceMetinEski = $_SESSION["nick"]."-".$_SESSION["ip"]."-Online";
      $replaceMetinYeni = $_SESSION["nick"]."-".$_SESSION["ip"]."-Online";
    }
    else {
      $replaceMetinEski = $_SESSION["nick"]."-".$_SESSION["ip"]."-Offline";
      $replaceMetinYeni = $_SESSION["nick"]."-".$_SESSION["ip"]."-Online";
    }
    $metin = str_replace($replaceMetinEski, $replaceMetinYeni, $metin);
    $file2 = fopen("online.txt","w+");
    $file3 = fopen("offline.txt","w+");
    fwrite($file2, $metin);
    fwrite($file3, $metin);
    fclose($file2);
    fclose($file3);
  }
  fclose($file);
  fclose($file4);
}

?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<div id="userList" class="container">
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
        else if($bol2[1] != $_SESSION["ip"] && isset($_SESSION[$ip][$digerIp]) && $_SESSION[$ip][$digerIp])
          echo '<input type="button" value="Sohbete Başla" onclick="basla(\''.$_SESSION["nick"].'\', \''.$_SESSION["ip"].'\', \''.$bol2[0].'\', \''.$bol2[1].'\')"></td>
        </tr>';
      }
    }
    ?>
  </table>
  <form action="cikis.php" method="POST">
    <input type="submit" name="disconnect" value="Oturumu Kapat">
  </form>
</div>
