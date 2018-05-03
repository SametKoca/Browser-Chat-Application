<?php

    session_start();
    $gelenIsteyenIp = $_GET["isteyenIp"];
    $gelenIstenenIp = $_GET["istenenIp"];
    if (!isset($_SESSION[$gelenIsteyenIp][$gelenIstenenIp]) || !$_SESSION[$gelenIsteyenIp][$gelenIstenenIp]) {
	    header("Location:index.php");
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <style type="text/css">
            
            .panel{
                margin-right: 3px;
            }
            
            .button {
                background-color: #0066FF;
                border: none;
                color: white;
	            margin-right: 30%;   
	            margin-left: 30%;
                text-decoration: none;
                display: block;
                font-size: 16px;
                cursor: pointer;
	            width:30%;
                height:40px;
	            margin-top: 5px;
            }


            input[type=text]{
		        width:100%;
	        }

            .chat_wrapper {
	            width: 60%;
	            height:530px;
	            margin-right: auto;
	            margin-left: auto;
	            background: #FF0000;
	            border: 1px solid #999999;
	            padding: 10px;
	            font: 14px 'lucida grande',tahoma,verdana,arial,sans-serif;
            }

            .chat_wrapper .message_box {
	            background: #F7F7F7;
	            height:350px;
		        overflow: auto;
	            padding: 10px 10px 20px 10px;
	            border: 1px solid #999999;
            }

            .chat_wrapper  input{
	            padding: 2px 2px 2px 5px;
            }

            .system_msg{color: #BDBDBD;font-style: italic;}
            
            .user_name{font-weight:bold;}
            
            .user_message{color: #88B6E0;}
            
            @media only screen and (max-width: 720px) {
                .chat_wrapper {
                    width: 95%;
	                height: 40%;
	            }
    
	            .button{ width:100%;
	                margin-right:auto;   
	                margin-left:auto;
	                height:40px;
	            }			
            }
        </style>
    </head>
    
    <body>	
        <?php 
            $colours = array('007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00');
            $user_colour = array_rand($colours);
        ?>

        <script type="text/javascript">
            
            $(document).ready(function(){
	            var wsUri = "ws://192.168.43.120:3500/BrowserMessenger/4/server.php"; //Write server.php path with your ip adress
	            websocket = new WebSocket(wsUri); 

	            websocket.onopen = function(ev) {
		            $('#message_box').append("<div class=\"system_msg\">Bağlandınız!</div>");
	            }

	            $("#oturumKapat").click(function() {
		            var isteyenIp = "<?php echo $_GET['isteyenIp']; ?>";
		            var istenenIp = "<?php echo $_GET['istenenIp']; ?>";
                    
                    $.ajax({
        	            type: "POST",
        	            data: "chatIsteyenIp=" + isteyenIp + "&chatIstenenIp=" + istenenIp,
        	            url: "chatsessiontemizle.php",
        	            success:function(result) {
        	            }
      	            });
                
                    var msg = {
        	            tip: 'chatClose',
        	            istenenIpp: istenenIp,
        	            isteyenIpp: isteyenIp 
                    };

                    websocket.send(JSON.stringify(msg));
		            window.close();
	            });
    
	            $('#send-btn').click(function(){
		            var mymessage = $('#message').val();
    
		            if(mymessage == ""){
		    	        alert("Boş mesaj gönderemezsiniz!");
		    	        return;
		            }
		
		            var objDiv = document.getElementById("message_box");
		            objDiv.scrollTop = objDiv.scrollHeight;

		            var msg = {
		                tip:'mesaj',
		                isteyenIp: '<?php echo $_GET["isteyenIp"]; ?>',
		                istenenIp: '<?php echo $_GET["istenenIp"]; ?>',
		                isteyenNick: '<?php echo $_GET["isteyenNick"]; ?>',
		                istenenNick: '<?php echo $_GET["istenenNick"]; ?>',
		                yazan: '<?php echo $_SESSION["nick"]; ?>',
		                mesaj: mymessage,
		                renk: '<?php echo $colours[$user_colour]; ?>'
		            };

		            websocket.send(JSON.stringify(msg));
	            });

	            websocket.onmessage = function(ev) {
		        
		            var msg = JSON.parse(ev.data);
		            var tip = msg.tip;

		            if (tip == "sessiontemizle") {
			            var isteyenIp = msg.isteyenIp;
			            var istenenIp = msg.istenenIp;
			            $.ajax({
        	                type: "POST",
        	                data: "chatIsteyenIp=" + isteyenIp + "&chatIstenenIp=" + istenenIp,
        	                url: "chatsessiontemizle.php",
        	                success:function(result) {
			    	            window.close();
        	                }
      	                });
		            }
    
		            if (tip == "mesajgeldi") {
		                var yazan = msg.yazanNick;
		                var mesaj = msg.mesaj;
		                var renk = msg.renk;
    
			            $('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+renk+"\">"+yazan+"</span> : <span class=\"user_message\">"+mesaj+"</span></div>");
		    
		                $('#message').val('');
		    
		                var objDiv = document.getElementById("message_box");
		                objDiv.scrollTop = objDiv.scrollHeight;
		                return;
		            }   
	            };

	            websocket.onerror = function(ev){$('#message_box').append("<div class=\"system_error\">Hata Oluştu - "+ev.data+"</div>");}; 
	            websocket.onclose = function(ev){$('#message_box').append("<div class=\"system_msg\">Bağlantı kesildi</div>");}; 
            });
        </script>
        
        <div class="chat_wrapper form-control">
            <div class="message_box form-control" id="message_box"></div>
            <br>
            <div class="panel">
                <input type="text" name="message" id="message" class="form-control" placeholder="Mesaj yaz" maxlength="80" 
                onkeydown = "if (event.keyCode == 13)document.getElementById('send-btn').click()"  />
            </div>
            
            <button id="send-btn" class="button btn">Gönder</button>
            <button id="oturumKapat" class="button btn">Sohbetten Çık</button>
        </div>
    </body>
</html>