<!doctype html>
<html lang="en">
  <head>
    <title>PRHIS</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- <link href="picture/favicon/icon10.svg" rel="shortcut icon" type="image/svg+xml" /> -->
    <!-- <link href="picture/favicon/icon2.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> -->
    <link href="include/favicon_io/favicon-32x32.png" rel="shortcut icon" type="image/vnd.microsoft.icon" /> 
    <!-- <link href="picture/favicon/icon12.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" /> -->
    <!-- <link href="picture/favicon/icon6.png" rel="shortcut icon" type="image/png" /> -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="project/themestr.app/theme_1568257930395.css">
    <link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.css">
    <link rel="stylesheet" href="project/login-page.css">
  </head>
  <body class="bg-info">
    <div class="container">
      <div class="row justify-content-md-center p-3">
        <div class="col-md-6 col-md-auto login-box pt-3 pb-5 bg-white">
          <h1 class="text-center text-primary">PRHIS Login</h1>
          <hr>
          <!-- <img src="project\images\logo kph 800.png" class="img-fluid ${3|rounded-top,rounded-right,rounded-bottom,rounded-left,rounded-circle,|}" alt=""> -->
          <img src="picture/favicon/icon17.svg" style="width: 100%" class="img-fluid ${3|rounded-top,rounded-right,rounded-bottom,rounded-left,rounded-circle,|}" alt="">
          <form action="checklogin.php" method="post">
            <div class="form-row">
              <div class="col-md-12">
                <input type="text" name="username" class="form-control form-control-lg" placeholder="username" autofocus>
              </div>
              <div class="col-md-12">
                <input type="password" name="password" class="form-control form-control-lg" placeholder="password">
              </div>
              <div class="col-md-12 pt-2">
                <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-sign-in-alt "></i> เข้าสู่ระบบ</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="vendor/components/jquery/jquery.slim.min.js"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
  </body>
</html>