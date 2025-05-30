<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/login.css" />
  <link rel="stylesheet" href="css/loader.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <title>Student Login</title>
  <style>
    body {
      background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
    }
    .loginform {
      width: min(400px, 95%);
      height: 500px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      border: 1px solid rgba(255, 255, 255, 0.18);
      padding: 40px;
    }
    .loginform:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.45);
    }
    .inputgroup {
      position: relative;
      height: 55px;
      width: 100%;
      margin-bottom: 25px;
    }
    .inputgroup input {
      width: 100%;
      height: 50px;
      background: #f5f5f5;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      color: #333;
      font-size: 16px;
      padding: 0 15px;
      transition: all 0.3s ease;
    }
    .inputgroup label {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #666;
      font-size: 16px;
      transition: all 0.3s ease;
      pointer-events: none;
      background: transparent;
      padding: 0 5px;
    }
    .inputgroup input:focus {
      border-color: #4361ee;
      background: #fff;
    }
    .inputgroup input:focus + label,
    .inputgroup input:valid + label {
      top: 0;
      font-size: 14px;
      color: #4361ee;
      background: #fff;
    }
    .btnlogin {
      width: 100%;
      height: 50px;
      background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 20px;
    }
    .btnlogin:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    .btnlogin.inactivecolor {
      background: #666;
      cursor: not-allowed;
    }
    .diverror {
      background-color: #ffebee;
      color: #d32f2f;
      height: 45px;
      width: 100%;
      border-radius: 10px;
      display: flex;
      justify-content: center;
      align-items: center;
      visibility: hidden;
      opacity: 0;
      transition: all 0.3s ease;
      font-size: 14px;
      margin-top: 20px;
      border: 1px solid #ffcdd2;
    }
    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }
    .login-header h1 {
      color: #333;
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .login-header p {
      color: #666;
      font-size: 16px;
    }
  </style>
</head>
<body>
  <form id="studentLoginForm">
    <div class="loginform">
      <div class="login-header">
        <h1>Student Login</h1>
        <p>Welcome back! Please login to your account.</p>
      </div>
      
      <div class="inputgroup">
        <input type="text" id="txtUsername" required autocomplete="email" />
        <label for="txtUsername" id="lblUsername">Email ID</label>
      </div>

      <div class="inputgroup">
        <input type="password" id="txtPassword" required autocomplete="current-password" />
        <label for="txtPassword" id="lblPassword">Password</label>
      </div>

      <div class="divcallforaction">
        <button type="submit" class="btnlogin inactivecolor" id="btnLogin">Login</button>
      </div>

      <div class="diverror" id="diverror" style="display: none;">
        <label class="errormessage" id="errormessage">ERROR GOES HERE</label>
      </div>
    </div>
  </form>

  <div class="lockscreen" id="lockscreen" style="display: none;">
    <div class="spinner" id="spinner"></div>
    <label class="lblwait topmargin" id="lblwait">Please wait...</label>
  </div>

  <script src="js/jquery.js"></script>
  <script src="js/Student.js"></script>
</body>
</html>
