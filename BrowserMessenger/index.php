<?php
session_start();
if(!isset($_SESSION["postrequest"])) {
    $_SESSION["postrequest"] = false;
}
if (!$_SESSION["postrequest"]) { 
    ?>

    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    
    <style type="text/css">
    
    .btn-file {
        position: relative;
        overflow: hidden;
    }

    .btn-file input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        background: white;
        cursor: inherit;
        display: block;
    }
    .tables td {
        padding-left: 30px;
        padding-right: 30px;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .content {
        max-width: 500px;
        margin: auto;
    }

</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript">
    
    var deger = document.getElementById("fileToUpload").value;
    alert("Bakalim");
    $.when(deger).then(function() {
        alert("Alert"); // Alerts 200
    });

</script>
<body class="content">  
    <div class="container">
        <form action="sohbet.php" method="POST" enctype="multipart/form-data">
            <div class="form-group" >	

                <table class="tables">
                   <thead>
                    <tr>
                        <td ><label for="usr">Nick:</label></td>
                        <td width="70%"><input type="text" name="nick" class="form-control" style="border: 1px solid black;"></td>
                    </tr>
                    <tr>
                        <td><label for="usr">Image:</label></td>
                        <input type="file" name="fileToUpload" class="btn btn-default btn-file" id="fileToUpload" style="display: none;">
                        <td><input type="button" value="Dosya Seç" class="btn btn-primary" onclick="document.getElementById('fileToUpload').click();"/></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Sohbete Katıl" name="submit" class="btn btn-primary" style="border: 1px solid black;"></td>
                    </tr>
                </thead>
            </table>


        </div>
    </form>


</div>
</body>
</html>

<?php } else {
    header("Location: sohbet.php");
}
?>