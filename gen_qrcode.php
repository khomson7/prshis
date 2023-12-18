<?php   require_once './include/Session.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./include/css/style.css">
</head>

<body>

    
        <section class="heading">
          <div class="title">QRcodes</div>
          <div class="sub-title">Generate QRCode for anything!</div>
        </section>
        <section class="user-input">
          <input type="text" placeholder="Type something..." name="input_text" id="input_text" autocomplete="off">
          <button class="button" type="submit">Generate<i class="fa-solid fa-rotate"></i></button>
        </section>
      
      <br />

        <div class="qr-code" style="display: none;"></div>
    
    

    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"> </script>
    <script src="./include/js/script.js"> </script>
</body>

</html>