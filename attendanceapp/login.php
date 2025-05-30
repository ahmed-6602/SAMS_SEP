<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/loader.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Login - Attendance System</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4caf50;
            --danger-color: #f44336;
            --text-primary: #2b2d42;
            --text-secondary: #6c757d;
            --background-color: #f0f2f5;
            --card-background: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            padding: 2rem;
        }

        .loginform {
            width: min(400px, 95%);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 2.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .loginform:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.45);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .inputgroup {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .inputgroup input {
            width: 100%;
            height: 50px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            padding: 0 1rem;
            transition: all 0.3s ease;
        }

        .inputgroup input:focus {
            border-color: var(--primary-color);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .inputgroup label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            pointer-events: none;
            background: transparent;
            padding: 0 0.5rem;
        }

        .inputgroup input:focus + label,
        .inputgroup input:valid + label {
            top: 0;
            font-size: 0.8rem;
            color: var(--primary-color);
            background: #fff;
        }

        .btnlogin {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
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
            color: var(--danger-color);
            height: 45px;
            width: 100%;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            margin-top: 1rem;
            border: 1px solid #ffcdd2;
        }

        .diverror.visible {
            visibility: visible;
            opacity: 1;
        }

        .lockscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .lockscreen.visible {
            display: flex;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .lblwait {
            color: white;
            margin-top: 1rem;
            font-size: 1rem;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .loginform {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="loginform">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please login to your account</p>
        </div>

        <div class="inputgroup">
            <input type="text" id="txtUsername" required autocomplete="username">
            <label for="txtUsername" id="lblUsername">Username</label>
        </div>

        <div class="inputgroup">
            <input type="password" id="txtPassword" required autocomplete="current-password">
            <label for="txtPassword" id="lblPassword">Password</label>
        </div>

        <div class="divcallforaction">
            <button type="submit" class="btnlogin inactivecolor" id="btnLogin">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </div>

        <div class="diverror" id="diverror">
            <label class="errormessage" id="errormessage">ERROR GOES HERE</label>
        </div>
    </div>

    <div class="lockscreen" id="lockscreen">
        <div class="spinner" id="spinner"></div>
        <label class="lblwait" id="lblwait">Please wait...</label>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/login.js"></script>
</body>
</html>