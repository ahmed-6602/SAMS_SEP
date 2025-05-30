<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/login.css" />
  <link rel="stylesheet" href="css/loader.css" />
  <title>Student Login</title>
</head>
<body>
  <form id="studentLoginForm">
    <div class="loginform">
      <div class="inputgroup topmarginlarge">
        <input type="text" id="txtUsername" required autocomplete="email" />
        <label for="txtUsername" id="lblUsername">EMAIL ID</label>
      </div>

      <div class="inputgroup topmarginlarge">
        <input type="password" id="txtPassword" required autocomplete="current-password" />
        <label for="txtPassword" id="lblPassword">PASSWORD</label>
      </div>

      <div class="divcallforaction topmarginlarge">
        <button type="submit" class="btnlogin inactivecolor" id="btnLogin">LOGIN</button>
      </div>

      <div class="diverror topmarginlarge" id="diverror" style="display: none;">
        <label class="errormessage" id="errormessage">ERROR GOES HERE</label>
      </div>
    </div>
  </form>

  <div class="lockscreen" id="lockscreen" style="display: none;">
    <div class="spinner" id="spinner"></div>
    <label class="lblwait topmargin" id="lblwait">PLEASE WAIT</label>
  </div>

  <script src="js/jquery.js"></script>
  <script src="js/Student.js"></script>
</body>
</html>
