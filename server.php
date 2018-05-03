<?php
session_start();
$host = '192.168.43.120'; //Your ip adress
$port = '3500';
$null = NULL;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, $port);
socket_listen($socket);
$clients = array($socket);
$file = fopen("online.txt","w+");
$file2 = fopen("offline.txt","w+");

$resimdata;

fclose($file);
fclose($file2);
//$degisim=0;

while (true) {

	$changed = $clients;
	socket_select($changed, $null, $null, 0, 10);

	if (in_array($socket, $changed)) {
		echo "Deneme yazisi\n";
		$socket_new = socket_accept($socket);
		$clients[] = $socket_new;
		
		$header = socket_read($socket_new, 1024);
		perform_handshaking($header, $socket_new, $host, $port);
		
		socket_getpeername($socket_new, $ip);
		$_SESSION[$ip] = $socket_new;
		echo $ip." sohbete katildi. - Socket : ".$socket_new."\r\n";

		$file = fopen("online.txt","a+");
		$file4 = fopen("offline.txt","a+");
		$metin = "";
		$user = "";
		while(!feof($file)) {
			$satir = fgets($file);
			$metin.=$satir;
			$bol = explode("\r\n", $satir);
			for($i=0; $i < count($bol) - 1; $i++) {
				$bol2 = explode("-", $bol[$i]);
				if ($bol2[1] == $ip)
					$user = $bol2[0];
			}
		}
		$replaceMetinEski = $user."-".$ip."-Offline";
		$replaceMetinYeni = $user."-".$ip."-Online";
		$metin = str_replace($replaceMetinEski, $replaceMetinYeni, $metin);
		$file2 = fopen("online.txt","w+");
		$file3 = fopen("offline.txt","w+");
		fwrite($file2, $metin);
		fwrite($file3, $metin);
		fclose($file2);
		fclose($file3);
		fclose($file);
		fclose($file4);

		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
		
	}
	foreach ($changed as $changed_socket) {	
		while(socket_recv($changed_socket, $buf, 10240000, 0) >= 1)
		{

			$received_text = unmask($buf);
			$tst_msg = json_decode($received_text);
			if ($tst_msg != $null && $tst_msg != "" && !empty($tst_msg)) {
				$tip = $tst_msg->tip;
				$isteyenIp = $tst_msg->isteyenIp;
				$istenenIp = $tst_msg->istenenIp;
				$isteyenNick = $tst_msg->isteyenNick;
				$istenenNick = $tst_msg->istenenNick;
				echo "TIP : ".$tip;
				if ($tip == "izin") {
					$response_text = mask(json_encode(array('tip'=>'izin', 'isteyenNick'=>$isteyenNick, 'isteyen'=>$isteyenIp, 'istenen'=>$istenenIp, 'istenenNick'=>$istenenNick)));
					send_message($response_text, $_SESSION[$istenenIp]);
					break 2;
				}

				if ($tip == "izincevap") {
					$sockets = array();
					$cevap = $tst_msg->cevap;
					
					if ($cevap) {
						$sockets[] = $_SESSION[$istenenIp];
						$sockets[] = $_SESSION[$isteyenIp];
						$response_text = mask(json_encode(array('tip'=>'izinverildi', 'isteyen'=>$isteyenIp, 'istenen'=>$istenenIp, 'isteyenNick'=>$isteyenNick, 'istenenNick'=>$istenenNick)));
						send_message($response_text, $sockets);
						break 2;
					}
					else {
						$response_text = mask(json_encode(array('tip'=>'izinverilmedi', 'isteyen'=>$isteyenIp, 'istenen'=>$istenenIp, 'isteyenNick'=>$isteyenNick, 'istenenNick'=>$istenenNick)));
						send_message($response_text, $_SESSION[$isteyenIp]);
						break 2;
					}
				}

				if ($tip == "mesaj") {
					$sockets = array();
					$yazan = $tst_msg->yazan;
					$mesaj = $tst_msg->mesaj;
					$renk = $tst_msg->renk;
					$sockets[] = $_SESSION[$istenenIp];
					$sockets[] = $_SESSION[$isteyenIp];
					$response_text = mask(json_encode(array('tip'=>'mesajgeldi', 'yazanNick'=>$yazan, 'mesaj'=>$mesaj, 'renk'=>$renk)));
					send_message($response_text, $sockets);
					break 2;
				}
				
				if ($tip == "resim") {
					echo "Resim geldi...";
					$imageCode = $tst_msg->imageCode;
					$rip = $tst_msg->rip;
					echo "Gelen Data : ".$rip."\n";              

					$data = explode( ',', $imageCode );
					$data2 = base64_decode($data[1]);
					
					$filepath = $rip.".jpeg";
					

					if (file_exists($filepath)){
						unlink($filepath);
					}


					file_put_contents($filepath,$data2);
					break 2;
				}
				if ($tip == "chatClose") {
					$sockets = array();
					$istenenIpp = $tst_msg->istenenIpp;
					$isteyenIpp = $tst_msg->isteyenIpp;
					echo $isteyenIpp." - ".$istenenIpp;
					$sockets[] = $_SESSION[$istenenIpp];
					$sockets[] = $_SESSION[$isteyenIpp];
					$response_text = mask(json_encode(array('tip'=>'sessiontemizle', 'isteyenIp' => $isteyenIpp, 'istenenIp' => $istenenIpp)));
					send_message($response_text, $sockets);
					break 2;
				}
			}
		}
		
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) {
			socket_getpeername($changed_socket, $ip);
			echo $ip." sohbetten ayrildi.\r\n";
			$file = fopen("offline.txt","a+");
			$metin = "";
			$durum = "";
			while(!feof($file)) {
				$satir = fgets($file);
				$metin.=$satir;
			}			
			$bol = explode("\r\n", $metin);
			for($i=0; $i < count($bol) - 1; $i++) {        
				$bol2 = explode("-", $bol[$i]);
				if(!$bol2) {
					echo "emptyyy";
				}
				if ($bol2[1] == $ip){ 
					$replaceMetinYeni = "";
					$replaceMetinEski = "";
					if($bol2[2] == "Online") {
						$replaceMetinYeni = $bol2[0]."-".$ip."-Offline";
						$replaceMetinEski = $bol2[0]."-".$ip."-Online";
					}
					else {
						$replaceMetinYeni = $bol2[0]."-".$ip."-Offline";
						$replaceMetinEski = $bol2[0]."-".$ip."-Offline";
					}
					$metin = str_replace($replaceMetinEski, $replaceMetinYeni, $metin);
				}                 
			}
			$file2 = fopen("offline.txt","w+");
			fwrite($file2, $metin);
			fclose($file2);
			fclose($file);
			
			$found_socket = array_search($changed_socket, $clients);
			unset($clients[$found_socket]);
			
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
			send_message($response, null);
		}
	}
}
socket_close($socket);
function send_message($msg, $socket)
{
	if(count($socket) > 1) {
		foreach($socket as $changed_socket)
		{
			@socket_write($changed_socket,$msg,strlen($msg));
		}

	}else {
		echo count($socket)."\r\n";
		@socket_write($socket,$msg,strlen($msg));
	}
	return true;
}
function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}
function perform_handshaking($receved_header,$client_conn, $host, $port)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host\r\n" .
		"WebSocket-Location: ws://$host:$port/demo/shout.php\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		socket_write($client_conn,$upgrade,strlen($upgrade));
	}
	?>